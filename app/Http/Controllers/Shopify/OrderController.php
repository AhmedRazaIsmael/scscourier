<?php

namespace App\Http\Controllers\Shopify;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Customer;
use App\Models\Booking;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->shop;
        $pak_cities = Cache::rememberForever('pak_cities_list', function() {
            $cities = $this->getPakCities();
            // Create a collection
            return collect($cities)->mapWithKeys(function ($city) {
                // key is 'karachi', value is 'Karachi'
                return [strtolower(trim($city)) => $city];
            })->toArray();
        });
        return view('orders.index', compact('pak_cities', 'shop'));
    }

    public function list(Request $request) 
    {
        $user = User::where('name', $request->shop)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid shop'], 404);
        }

        $shop = $user->name;
        $accessToken = $user->password;

        $limit = $request->input('limit', 50);
        $cursor = $request->input('cursor');
        $direction = $request->input('direction', 'next'); // 'next' or 'previous'

        $variables = [];
        $paginationArgs = '';

        try {
            if ($direction === 'next') {
                $paginationArgs = 'first: ' . $limit;
                // Check if cursor is present and not the literal string "null"
                if ($cursor && $cursor !== 'null' && $cursor !== '') {
                    $paginationArgs .= ', after: "' . $cursor . '"';
                }
            } else {
                $paginationArgs = 'last: ' . $limit;
                // Check if cursor is present and not the literal string "null"
                if ($cursor && $cursor !== 'null' && $cursor !== '') {
                    $paginationArgs .= ', before: "' . $cursor . '"';
                }
            }

            $query = <<<GRAPHQL
            query {
                orders($paginationArgs, query: "fulfillment_status:unfulfilled AND status:open AND NOT cancelled_at:*", sortKey: CREATED_AT, reverse: true) {
                    edges {
                        cursor
                        node {
                            id
                            name
                            createdAt
                            note
                            displayFinancialStatus
                            displayFulfillmentStatus
                            totalPriceSet {
                                shopMoney { amount currencyCode }
                            }
                            totalWeight
                            cancelledAt
                            shippingAddress {
                                name
                                address1
                                city
                                phone
                            }
                            lineItems(first: 25) {
                                edges {
                                    node {
                                        title
                                        quantity
                                        variant {
                                            title
                                            sku
                                            price
                                            selectedOptions {
                                                name
                                                value
                                            }
                                            inventoryItem {
                                                measurement {
                                                    weight {
                                                        value
                                                        unit
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    pageInfo {
                        hasNextPage
                        hasPreviousPage
                        startCursor
                        endCursor
                    }
                }
            }
            GRAPHQL;

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://$shop/admin/api/2024-01/graphql.json", [
                'query' => $query,
                'variables' => (object) $variables,
            ]);

            Log::error('Shopify raw response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if (!$response->ok()) {
                return response()->json([
                    'error' => 'Shopify API error',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ], 500);
            }

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch orders'], 500);
            }
            return response()->json($response->json('data.orders'));
        } catch (\Exception $e) {
            Log::error('Error fetching orders shopify app: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cities(Request $request)
    {
        $customer = $request->user();
        $api = $customer->api();

        $query = <<<GQL
        {
          orders(first: 250, sortKey: CREATED_AT, reverse: true) {
            edges {
              node {
                shippingAddress {
                  city
                }
              }
            }
          }
        }
        GQL;

        $result = $api->graph($query);
        $orders = $result['body']['data']['orders']['edges'] ?? [];

        $cities = collect($orders)
            ->pluck('node.shippingAddress.city')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        // dd($cities);

        return response()->json($cities);
    }

    public function reauth(Request $request)
    {
        // Get shop domain from authenticated session or query param
        $shopDomain = $request->get('shop') ?? optional($request->user())->getDomain()->toNative();

        if (! $shopDomain) {
            abort(400, 'Shop domain is missing.');
        }

        // Redirect to OAuth install/reauth URL
        return redirect()->route('authenticate', ['shop' => $shopDomain]);
    }

    public function showBulkBooking(Request $request)
    {
        $validated = $request->validate([
            'shop' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'required|string',
        ]);
        
        $shopDomain = $validated['shop'];
        $orderIds = $validated['ids'];
        
        try {
            // Get shop access token from database
            $shop = User::where('name', $shopDomain)->firstOrFail();
            
            // Fetch order details from Shopify
            $orders = $this->fetchOrdersFromShopify($shop, $orderIds);
            
            // Filter unfulfilled orders
            $unfulfilledOrders = collect($orders)->filter(function($order) {
                // Skip cancelled orders
                if (isset($order['cancelledAt']) && $order['cancelledAt'] !== null) {
                    return false;
                }
            
                // Skip already fulfilled orders
                $fulfillmentStatus = $order['displayFulfillmentStatus'] ?? '';
                return in_array($fulfillmentStatus, ['UNFULFILLED', 'PARTIALLY_FULFILLED']);
            })->values()->all();
            
            // Process orders for SCS Courier
            $processedOrders = $this->processOrdersForSCS($unfulfilledOrders);
            $cities = $this->getPakCities();
            
            $shopDetails = $this->fetchShopDetails($shop);

            return view('orders.bulk-upload', [
                'orders' => $processedOrders,
                'rawOrders' => $unfulfilledOrders,
                'unfulfilledCount' => count($unfulfilledOrders),
                'totalCount' => count($orders),
                'shop' => $shop,
                'shopDetails'  => $shopDetails,
                'cities' => $cities
            ]);
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to fetch orders: ' . $e->getMessage()]);
        }
    }

    private function fetchOrdersFromShopify($shop, $orderIds)
    {
        $api = $shop->api();
        $query = <<<'GRAPHQL'
        query Orders($ids: [ID!]!) {
          nodes(ids: $ids) {
            ... on Order {
              id
              name
              displayFinancialStatus
              displayFulfillmentStatus
              totalPriceSet {
                shopMoney { amount currencyCode }
              }
              totalWeight
              note
              cancelledAt
              shippingAddress {
                name
                address1
                city
                phone
              }
              lineItems(first: 25) {
                edges {
                  node {
                    title
                    quantity
                    variant {
                      title
                      sku
                      price
                      selectedOptions {
                        name
                        value
                      }
                      inventoryItem {
                        measurement {
                          weight {
                            value
                            unit
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
        GRAPHQL;
        
        $orderIds = array_map(function ($id) {
            return base64_encode("gid://shopify/Order/{$id}");
        }, $orderIds);
        
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->password,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop->name}/admin/api/2024-01/graphql.json", [
            'query' => $query,
            'variables' => ['ids' => $orderIds],
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Shopify API request failed: ' . $response->body());
        }
        
        $data = $response->json();
        
        if (isset($data['errors'])) {
            throw new \Exception('Shopify GraphQL Error: ' . json_encode($data['errors']));
        }
        
        return array_filter($data['data']['nodes'] ?? [], fn($node) => $node !== null);
    }
    
    private function processOrdersForSCS($orders)
    {
        return array_map(function($order) {
            $money = $order['totalPriceSet']['shopMoney'] ?? [];
            $isPaid = ($order['displayFinancialStatus'] ?? '') === 'PAID';
            $codAmount = $isPaid ? 0 : floatval($money['amount'] ?? 0);
            
            $address = $order['shippingAddress'] ?? [];
            $customer = $order['customer'] ?? [];
            
            // Calculate order details string and total pieces
            $orderDetails = '';
            $totalPieces = 0;
            
            if (!empty($order['lineItems']['edges'])) {
                $items = [];
                foreach ($order['lineItems']['edges'] as $edge) {
                    $node = $edge['node'] ?? [];

                    $quantity = $node['quantity'] ?? 1;

                    $totalPieces += $quantity;
                    $title    = $node['title'] ?? '';
                    $variant  = $node['variant'] ?? [];
                    $sku      = $variant['sku'] ?? '';

                    // Build variant text from selectedOptions (Size / Color / etc.)
                    $variantText = '';
                    if (!empty($variant['selectedOptions']) && is_array($variant['selectedOptions'])) {
                        $values = [];
                        foreach ($variant['selectedOptions'] as $opt) {
                            if (!empty($opt['value'])) {
                                $values[] = $opt['value'];
                            }
                        }
                        $variantText = implode(' / ', $values);
                    }

                    $itemStr = "[ {$quantity} x {$title}";

                    if ($sku) {
                        $itemStr .= " ({$sku})";
                    }

                    if ($variantText) {
                        $itemStr .= " - {$variantText}";
                    }

                    $itemStr .= " ]";

                    $items[] = $itemStr;
                }
                $orderDetails = implode(' ', $items);
            }
            
            // Calculate weight in KG
            $weight = $this->calculateOrderWeightKg($order);
            
            // Build full address
            $fullAddress = trim(($address['address1'] ?? '') . ' ' . ($address['address2'] ?? ''));
            
            return [
                'shopify_order_id' => $order['id'],
                'order_reference' => $order['name'],
                'order_date' => date('M d, Y', strtotime($order['createdAt'] ?? 'now')),
                'recipient_name' => $address['name'] ?? ($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? ''),
                'recipient_email' => $customer['email'] ?? '',
                'recipient_contact' => $address['phone'] ?? '',
                'delivery_address' => $fullAddress,
                'city' => $address['city'] ?? '',
                'province' => $address['province'] ?? '',
                'zip' => $address['zip'] ?? '',
                'country' => $address['country'] ?? '',
                'destination' => $address['city'] ?? '',
                'no_of_items' => $totalPieces,
                'order_remarks' => $order['note'] ?? '',
                'amount' => $codAmount,
                'order_details' => $orderDetails,
                'weight' => $weight,
                'currency' => $money['currencyCode'] ?? 'PKR',
                'financial_status' => $order['displayFinancialStatus'] ?? 'PENDING',
                'fulfillment_status' => $order['displayFulfillmentStatus'] ?? 'UNFULFILLED',
            ];
        }, $orders);
    }

    private function calculateOrderWeightKg($order)
    {
        if (!isset($order['lineItems']['edges'])) {
            return 0;
        }
        
        $totalWeight = 0;
        
        foreach ($order['lineItems']['edges'] as $edge) {
            $node = $edge['node'];
            $weightObj = $node['variant']['inventoryItem']['measurement']['weight'] ?? null;
            
            if (!$weightObj) {
                continue;
            }
            
            $value = floatval($weightObj['value'] ?? 0);
            $unit = $weightObj['unit'] ?? 'KILOGRAMS';
            
            // Normalize to KG
            $weightKg = match($unit) {
                'GRAMS' => $value / 1000,
                'POUNDS' => $value * 0.453592,
                'OUNCES' => $value * 0.0283495,
                default => $value, // KILOGRAMS
            };
            
            $quantity = $node['quantity'] ?? 1;
            $totalWeight += $weightKg * $quantity;
        }
        
        return round($totalWeight, 2);
    }
    
    private function shopifyGraphQL($shop, $query, $variables = [])
    {
        $endpoint = "https://{$shop->name}/admin/api/2024-01/graphql.json";
    
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->password, // access token
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'query' => $query
        ]);
    
        if ($response->failed()) {
            throw new \Exception('Shopify GraphQL request failed');
        }
    
        $data = $response->json();
    
        if (!empty($data['errors'])) {
            throw new \Exception($data['errors'][0]['message']);
        }
    
        return $data;
    }
    
    private function fetchShopDetails($shop)
    {
        $query = <<<GQL
        query {
            shop {
                id
                name
                email
            }
        }
        GQL;
    
        $response = $this->shopifyGraphQL($shop, $query);
    
        return $response['data']['shop'];
    }
    
    public function getPakCities()
    {
        $cities = [
            // Punjab
            'Lahore', 'Faisalabad', 'Rawalpindi', 'Multan', 'Gujranwala', 
            'Sialkot', 'Bahawalpur', 'Sargodha', 'Sahiwal', 'Gujrat', 
            'Jhang', 'Sheikhupura', 'Kasur', 'Okara', 'Dera Ghazi Khan', 
            'Chiniot', 'Mandi Bahauddin', 'Pakpattan', 'Rahim Yar Khan', 
            'Attock', 'Narowal', 'Toba Tek Singh', 'Muzaffargarh', 'Bhakkar', 
            'Khushab', 'Vehari', 'Chakwal', 'Burewala', 'Jhelum', 'Mianwali', 
            'Gujar Khan', 'Lodhran', 'Layyah', 'Sadiqabad', 'Khanewal', 'Chichawatni',
            'Havelian', 'Mianwali',
        
            // Sindh
            'Karachi', 'Hyderabad', 'Sukkur', 'Larkana', 'Mirpur Khas', 
            'Nawabshah', 'Jacobabad', 'Shikarpur', 'Khairpur', 'Badin', 
            'Tando Allahyar', 'Tando Muhammad Khan', 'Thatta', 'Umerkot', 
            'Dadu', 'Jamshoro', 'Sanghar', 'Ghotki', 'Matiari', 'Tando Adam', 'Tando Jam',
        
            // Khyber Pakhtunkhwa
            'Peshawar', 'Mardan', 'Abbottabad', 'Swat', 'Kohat', 'Dera Ismail Khan', 
            'Nowshera', 'Charsadda', 'Haripur', 'Battagram', 'Bannu', 'Karak', 
            'Hangu', 'Mansehra', 'Malakand', 'Swabi', 'Lower Dir', 'Upper Dir', 
            'Kohistan', 'Torghar', 'Buner', 'Chitral',
        
            // Balochistan
            'Quetta', 'Gwadar', 'Turbat', 'Sibi', 'Chaman', 'Kalat', 
            'Khuzdar', 'Zhob', 'Pishin', 'Nushki', 'Lasbela', 'Mastung', 'Jaffarabad', 
            'Kharan', 'Wazirabad',
            
            // Azad Kashmir
            'Kashmir', 'Koli', 'Mirpur AJK', 'Muzaffarabad', 'Gilgit',

            // Islamabad Capital Territory
            'Islamabad'
        ];
        sort($cities);
        return $cities;
    }

    // public function getOrders(Request $request)
    // {
    //     $user = $request->get('auth_user');

    //     $query = <<<GRAPHQL
    //     {
    //     orders(first: 20, query: "fulfillment_status:unfulfilled") {
    //         edges {
    //         node {
    //             id
    //             name
    //             createdAt
    //             totalPriceSet {
    //             shopMoney {
    //                 amount
    //             }
    //             }
    //             shippingAddress {
    //             name
    //             address1
    //             city
    //             country
    //             phone
    //             }
    //         }
    //         }
    //     }
    //     }
    //     GRAPHQL;

    //     $response = Http::withHeaders([
    //         'X-Shopify-Access-Token' => $user->shopify_access_token,
    //         'Content-Type' => 'application/json'
    //     ])->post("https://{$user->shop_domain}/admin/api/2024-01/graphql.json", [
    //         'query' => $query
    //     ]);

    //     return $response->json();
    // }

    // public function getOrders(Request $request)
    // {
    //     // 🔐 Step 1 — Verify Shopify session token
    //     $authHeader = $request->header('Authorization');

    //     if (!$authHeader) {
    //         return response()->json(['error' => 'Missing session token'], 401);
    //     }

    //     $jwt = str_replace('Bearer ', '', $authHeader);

    //     try {
    //         $decoded = JWT::decode(
    //             $jwt,
    //             new Key(config('services.shopify.secret'), 'HS256')
    //         );

    //         $shop = str_replace('https://', '', $decoded->dest);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Invalid session token'], 401);
    //     }

    //     // 🔎 Step 2 — Get user with this shop
    //     $user = User::where('shop_domain', $shop)->first();

    //     if (!$user || !$user->shopify_access_token) {
    //         return response()->json([
    //             'error' => 'Store not connected'
    //         ], 400);
    //     }

    //     // 📦 Step 3 — Fetch Orders from Shopify
    //     $response = Http::withHeaders([
    //         'X-Shopify-Access-Token' => $user->shopify_access_token,
    //         'Content-Type' => 'application/json'
    //     ])->post("https://{$shop}/admin/api/2024-01/graphql.json", [
    //         'query' => '
    //         {
    //           orders(first: 20, sortKey: CREATED_AT, reverse: true) {
    //             edges {
    //               node {
    //                 id
    //                 name
    //                 createdAt
    //                 displayFinancialStatus
    //                 displayFulfillmentStatus
    //                 totalPriceSet {
    //                   shopMoney {
    //                     amount
    //                     currencyCode
    //                   }
    //                 }
    //                 customer {
    //                   firstName
    //                   lastName
    //                   email
    //                 }
    //               }
    //             }
    //           }
    //         }
    //         '
    //     ]);

    //     if (!$response->successful()) {
    //         return response()->json([
    //             'error' => 'Failed to fetch orders',
    //             'details' => $response->body()
    //         ], 500);
    //     }

    //     return response()->json($response->json());
    // }

    public function getOrders(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Get app_token From Authorization Header
        |--------------------------------------------------------------------------
        */

        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['error' => 'Missing Authorization header'], 401);
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Invalid Authorization format'], 401);
        }

        $appToken = str_replace('Bearer ', '', $authHeader);

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Find User Using app_token
        |--------------------------------------------------------------------------
        */

        $user = User::where('app_token', $appToken)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid app token'], 401);
        }

        $shop = $user->shop_domain;

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Get Shop Record
        |--------------------------------------------------------------------------
        */

        $shopRecord = Shop::where('shop_domain', $shop)->first();

        if (!$shopRecord || !$shopRecord->shopify_access_token) {
            return response()->json([
                'error' => 'Store not connected'
            ], 400);
        }
        // /*
        // |--------------------------------------------------------------------------
        // | 🔐 Step 1 — Verify Shopify Session Token (JWT)
        // |--------------------------------------------------------------------------
        // */

        // $authHeader = $request->header('Authorization');

        // if (!$authHeader) {
        //     return response()->json(['error' => 'Missing session token'], 401);
        // }

        // $jwt = str_replace('Bearer ', '', $authHeader);

        // try {
        //     $decoded = JWT::decode(
        //         $jwt,
        //         new Key(config('services.shopify.secret'), 'HS256')
        //     );

        //     $shop = str_replace('https://', '', $decoded->dest);

        // } catch (\Exception $e) {
        //     return response()->json(['error' => 'Invalid session token'], 401);
        // }

        // /*
        // |--------------------------------------------------------------------------
        // | 🏬 Step 2 — Get Shop Record
        // |--------------------------------------------------------------------------
        // */

        // $shopRecord = Shop::where('shop_domain', $shop)->first();

        // if (!$shopRecord || !$shopRecord->shopify_access_token) {
        //     return response()->json([
        //         'error' => 'Store not connected'
        //     ], 400);
        // }

        /*
        |--------------------------------------------------------------------------
        | 📄 Step 3 — Pagination Support
        |--------------------------------------------------------------------------
        */

        $limit = $request->input('limit', 20);
        $cursor = $request->input('cursor');
        $direction = $request->input('direction', 'next');

        $paginationArgs = '';

        if ($direction === 'next') {
            $paginationArgs = "first: {$limit}";
            if ($cursor) {
                $paginationArgs .= ', after: "' . $cursor . '"';
            }
        } else {
            $paginationArgs = "last: {$limit}";
            if ($cursor) {
                $paginationArgs .= ', before: "' . $cursor . '"';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 📦 Step 4 — GraphQL Query (Courier Friendly)
        |--------------------------------------------------------------------------
        */

        $query = <<<GRAPHQL
        query {
            orders(
                $paginationArgs,
                query: "fulfillment_status:unfulfilled AND status:open AND NOT cancelled_at:*",
                sortKey: CREATED_AT,
                reverse: true
            ) {
                edges {
                    cursor
                    node {
                        id
                        name
                        createdAt
                        note
                        currentTotalLineItemsQuantity
                        noteAttributes {
                            name
                            value
                        }
                        displayFinancialStatus
                        displayFulfillmentStatus
                        totalPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        shippingAddress {
                            name
                            address1
                            city
                            province
                            country
                            zip
                            phone
                        }
                        billingAddress {
                            country
                        }    
                        lineItems(first: 25) {
                            edges {
                                node {
                                    title
                                    quantity
                                }
                            }
                        }
                    }
                }
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
            }
        }
        GRAPHQL;

        /*
        |--------------------------------------------------------------------------
        | 🌐 Step 5 — Call Shopify
        |--------------------------------------------------------------------------
        */

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopRecord->shopify_access_token,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop}/admin/api/2024-01/graphql.json", [
            'query' => $query
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to fetch orders from Shopify',
                'details' => $response->body()
            ], 500);
        }

        $data = $response->json();

        if (isset($data['errors'])) {
            return response()->json([
                'error' => 'Shopify GraphQL error',
                'details' => $data['errors']
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔄 Step 6 — Transform Data For Frontend
        |--------------------------------------------------------------------------
        */

        // $orders = collect($data['data']['orders']['edges'])
        //     ->map(function ($edge) {
        //         $node = $edge['node'];

        //         return [
        //             'id' => $node['id'],
        //             'order_number' => $node['name'],
        //             'created_at' => $node['createdAt'],
        //             'financial_status' => $node['displayFinancialStatus'],
        //             'fulfillment_status' => $node['displayFulfillmentStatus'],
        //             'amount' => $node['totalPriceSet']['shopMoney']['amount'],
        //             'currency' => $node['totalPriceSet']['shopMoney']['currencyCode'],
        //             'shipping' => [
        //                 'name' => $node['shippingAddress']['name'] ?? '',
        //                 'address1' => $node['shippingAddress']['address1'] ?? '',
        //                 'city' => $node['shippingAddress']['city'] ?? '',
        //                 'province' => $node['shippingAddress']['province'] ?? '',
        //                 'country' => $node['shippingAddress']['country'] ?? '',
        //                 'zip' => $node['shippingAddress']['zip'] ?? '',
        //                 'phone' => $node['shippingAddress']['phone'] ?? '',
        //             ],
        //             'items' => collect($node['lineItems']['edges'])
        //                 ->map(function ($item) {
        //                     return [
        //                         'title' => $item['node']['title'],
        //                         'quantity' => $item['node']['quantity'],
        //                     ];
        //                 })->values(),
        //             'cursor' => $edge['cursor']
        //         ];
        //     })->values();

        $orders = collect($data['data']['orders']['edges'])
        ->map(function ($edge) {

            $node = $edge['node'];

            $shippingAddress = $node['shippingAddress'] ?? [];
            $billingAddress  = $node['billingAddress'] ?? [];

            return [

                'id' => $node['id'] ?? null,
                'order_number' => $node['name'] ?? null,
                'created_at' => $node['createdAt'] ?? null,

                'financial_status' => $node['displayFinancialStatus'] ?? null,
                'fulfillment_status' => $node['displayFulfillmentStatus'] ?? null,

                'amount' => $node['totalPriceSet']['shopMoney']['amount'] ?? 0,
                'currency' => $node['totalPriceSet']['shopMoney']['currencyCode'] ?? null,

                // ✅ Order Note
                'order_note' => $node['note'] ?? '',

                // ✅ Country (Shipping → Billing fallback)
                'country' => $shippingAddress['country'] 
                                ?? $billingAddress['country'] 
                                ?? '',

                // ✅ Shopify-style Items Count
                'items_count' => $node['currentTotalLineItemsQuantity'] ?? 0,

                // ✅ Shipping Info
                'shipping' => [
                    'name'     => $shippingAddress['name'] ?? '',
                    'address1' => $shippingAddress['address1'] ?? '',
                    'city'     => $shippingAddress['city'] ?? '',
                    'province' => $shippingAddress['province'] ?? '',
                    'country'  => $shippingAddress['country'] 
                                    ?? $billingAddress['country'] 
                                    ?? '',
                    'zip'      => $shippingAddress['zip'] ?? '',
                    'phone'    => $shippingAddress['phone'] ?? '',
                ],

                // ✅ Line Items List
                'items' => collect($node['lineItems']['edges'] ?? [])
                    ->map(function ($item) {
                        return [
                            'title'    => $item['node']['title'] ?? '',
                            'quantity' => $item['node']['quantity'] ?? 0,
                        ];
                    })->values(),

                'cursor' => $edge['cursor'] ?? null,
            ];
        })
        ->values();

        /*
        |--------------------------------------------------------------------------
        | 📤 Step 7 — Return Clean Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'orders' => $orders,
            'pageInfo' => $data['data']['orders']['pageInfo']
        ]);
    }


    // public function pushOrders(Request $request)
    // {
    //     $user = $request->get('auth_user');

    //     if (!$user || !$user->shopify_access_token) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $request->validate([
    //         'orders' => 'required|array|min:1',
    //         'orders.*' => 'required|string'
    //     ]);

    //     $shop = $user->shop_domain;
    //     $accessToken = $user->shopify_access_token;

    //     $createdBookings = [];

    //     foreach ($request->orders as $orderId) {

    //         // 1️⃣ Fetch full order from Shopify
    //         $response = Http::withHeaders([
    //             'X-Shopify-Access-Token' => $accessToken,
    //             'Content-Type' => 'application/json'
    //         ])->post("https://{$shop}/admin/api/2024-01/graphql.json", [
    //             'query' => '
    //             query getOrder($id: ID!) {
    //                 order(id: $id) {
    //                     id
    //                     name
    //                     totalPriceSet {
    //                         shopMoney { amount currencyCode }
    //                     }
    //                     shippingAddress {
    //                         name
    //                         address1
    //                         city
    //                         province
    //                         country
    //                         zip
    //                         phone
    //                     }
    //                     lineItems(first: 10) {
    //                         edges {
    //                             node {
    //                                 title
    //                                 quantity
    //                             }
    //                         }
    //                     }
    //                 }
    //             }',
    //             'variables' => ['id' => $orderId]
    //         ]);

    //         if (!$response->successful()) {
    //             continue;
    //         }

    //         $order = $response->json()['data']['order'] ?? null;

    //         if (!$order || !$order['shippingAddress']) {
    //             continue; // skip digital orders
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 2️⃣ Create / Find Customer
    //         |--------------------------------------------------------------------------
    //         */

    //         $customer = \App\Models\Customer::firstOrCreate(
    //             ['contact_no_1' => $order['shippingAddress']['phone'] ?? uniqid()],
    //             [
    //                 'contact_person_1' => $order['shippingAddress']['name'],
    //                 'address_1'        => $order['shippingAddress']['address1'],
    //                 'email_1'          => null,
    //             ]
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 3️⃣ Generate Booking Number
    //         |--------------------------------------------------------------------------
    //         */

    //         $prefix  = 'SB';
    //         $year    = date('y');
    //         $month   = date('m');
    //         $random  = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    //         $bookNo  = "{$prefix}{$year}{$month}{$random}";

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 4️⃣ Prepare Booking Data
    //         |--------------------------------------------------------------------------
    //         */

    //         $bookingData = [
    //             'customer_id'    => $customer->id,
    //             'bookingType'    => 'domestic',
    //             'paymentMode'    => 'cod',
    //             'destination'    => $order['shippingAddress']['city'],
    //             'destinationCountry' => $order['shippingAddress']['country'],
    //             'invoiceValue'   => $order['totalPriceSet']['shopMoney']['amount'],
    //             'weight'         => 1,
    //             'pieces'         => 1,
    //             'orderNo'        => $order['name'],
    //             'consigneeName'  => $order['shippingAddress']['name'],
    //             'consigneeNumber'=> $order['shippingAddress']['phone'],
    //             'consigneeAddress'=> $order['shippingAddress']['address1'],
    //             'bookNo'         => $bookNo,
    //             'bookDate'       => now()->toDateString(),
    //         ];

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 5️⃣ Save Booking
    //         |--------------------------------------------------------------------------
    //         */

    //         $booking = \App\Models\Booking::create($bookingData);

    //         $createdBookings[] = [
    //             'shopify_order' => $order['name'],
    //             'booking_no'    => $bookNo
    //         ];

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 6️⃣ OPTIONAL: Mark Shopify Order as Fulfilled
    //         |--------------------------------------------------------------------------
    //         */
    //         // We can add fulfillment mutation here later
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'bookings_created' => $createdBookings
    //     ]);
    // }

    // public function pushOrders(Request $request)
    // {
    //     /*
    //     |--------------------------------------------------------------------------
    //     | 1️⃣ Verify Shopify Session JWT
    //     |--------------------------------------------------------------------------
    //     */

    //     $authHeader = $request->header('Authorization');

    //     if (!$authHeader) {
    //         return response()->json(['error' => 'Missing session token'], 401);
    //     }

    //     $jwt = str_replace('Bearer ', '', $authHeader);

    //     try {
    //         $decoded = \Firebase\JWT\JWT::decode(
    //             $jwt,
    //             new \Firebase\JWT\Key(config('services.shopify.secret'), 'HS256')
    //         );

    //         $shop = str_replace('https://', '', $decoded->dest);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Invalid session token',
    //             'message' => $e->getMessage()
    //         ], 401);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 2️⃣ Get Customer ID Using shop_domain
    //     |--------------------------------------------------------------------------
    //     */

    //     $user = User::where('shop_domain', $shop)->first();

    //     if (!$user) {
    //         return response()->json(['error' => 'Customer not found for this shop'], 404);
    //     }

    //     $customerId = $user->id;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 3️⃣ Validate Orders Payload
    //     |--------------------------------------------------------------------------
    //     */

    //     $request->validate([
    //         'orders' => 'required|array|min:1',
    //         'orders.*.order_number' => 'required|string',
    //         'orders.*.financial_status' => 'required|string',
    //         'orders.*.amount' => 'required',
    //     ]);

    //     $createdBookings = [];

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 4️⃣ Loop Orders
    //     |--------------------------------------------------------------------------
    //     */

    //     foreach ($request->orders as $order) {

    //         if (empty($order['shipping'])) {
    //             continue;
    //         }

    //         $orderNo = ltrim($order['order_number'], '#');

    //         // Prevent duplicate
    //         if (Booking::where('orderNo', $orderNo)->exists()) {
    //             continue;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 5️⃣ Generate Booking Number
    //         |--------------------------------------------------------------------------
    //         */

    //         $bookingType = 'domestic';
    //         $typeCode    = '01';

    //         $prefix  = 'AB';
    //         $year    = date('y');
    //         $month   = date('m');
    //         $random  = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    //         $bookNo  = "{$prefix}{$year}{$month}{$typeCode}{$random}";

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 6️⃣ Detect Payment Mode
    //         |--------------------------------------------------------------------------
    //         */

    //         $paymentMode = $order['financial_status'] === 'PAID'
    //             ? 'non_cod'
    //             : 'cod';

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 7️⃣ Create Booking
    //         |--------------------------------------------------------------------------
    //         */

    //         Booking::create([
    //             'customer_id'        => $customerId, // 🔥 from shop_domain
    //             'bookingType'        => $bookingType,
    //             'paymentMode'        => $paymentMode,
    //             'destination'        => $order['shipping']['city'] ?? '',
    //             'destinationCountry' => $order['shipping']['country'] ?? '',
    //             'invoiceValue'       => $order['amount'],
    //             'weight'             => 1,
    //             'pieces'             => 1,
    //             'orderNo'            => $orderNo,

    //             // Shopify buyer stored as consignee
    //             'consigneeName'      => $order['shipping']['name'] ?? '',
    //             'consigneeNumber'    => $order['shipping']['phone'] ?? '',
    //             'consigneeAddress'   => $order['shipping']['address1'] ?? '',
    //             'consigneeEmail'     => null,

    //             'bookNo'             => $bookNo,
    //             'bookDate'           => now()->toDateString(),
    //         ]);

    //         $createdBookings[] = [
    //             'shopify_order' => $orderNo,
    //             'booking_no'    => $bookNo
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'bookings_created' => $createdBookings
    //     ]);
    // }

    // public function pushOrders(Request $request)
    // {
    //     /*
    //     |--------------------------------------------------------------------------
    //     | 1️⃣ Validate App Token + Orders
    //     |--------------------------------------------------------------------------
    //     */

    //     $request->validate([
    //         'app_token' => 'required|string',
    //         'orders' => 'required|array|min:1',
    //         'orders.*.order_number' => 'required|string',
    //         'orders.*.financial_status' => 'required|string',
    //         'orders.*.amount' => 'required',
    //     ]);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 2️⃣ Get User Using app_token
    //     |--------------------------------------------------------------------------
    //     */

    //     $user = User::where('app_token', $request->app_token)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'error' => 'Invalid app token'
    //         ], 401);
    //     }

    //     $customerId = $user->id;
    //     $shop       = $user->shop_domain; // optional if needed later

    //     $createdBookings = [];

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 3️⃣ Loop Orders
    //     |--------------------------------------------------------------------------
    //     */

    //     foreach ($request->orders as $order) {

    //         if (empty($order['shipping'])) {
    //             continue;
    //         }

    //         $orderNo = ltrim($order['order_number'], '#');

    //         // Prevent duplicate
    //         if (Booking::where('orderNo', $orderNo)->exists()) {
    //             continue;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 4️⃣ Generate Booking Number
    //         |--------------------------------------------------------------------------
    //         */

    //         $bookingType = 'domestic';
    //         $typeCode    = '01';

    //         $prefix  = 'AB';
    //         $year    = date('y');
    //         $month   = date('m');
    //         $random  = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    //         $bookNo  = "{$prefix}{$year}{$month}{$typeCode}{$random}";

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 5️⃣ Detect Payment Mode
    //         |--------------------------------------------------------------------------
    //         */

    //         $paymentMode = $order['financial_status'] === 'PAID'
    //             ? 'non_cod'
    //             : 'cod';

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 6️⃣ Create Booking
    //         |--------------------------------------------------------------------------
    //         */

    //         Booking::create([
    //             'customer_id'        => $customerId, // from app_token user
    //             'bookingType'        => $bookingType,
    //             'paymentMode'        => $paymentMode,
    //             'destination'        => $order['shipping']['city'] ?? '',
    //             'destinationCountry' => $order['shipping']['country'] ?? '',
    //             'invoiceValue'       => $order['amount'],
    //             'weight'             => 1,
    //             'pieces'             => 1,
    //             'orderNo'            => $orderNo,

    //             // Shopify buyer stored as consignee
    //             'consigneeName'      => $order['shipping']['name'] ?? '',
    //             'consigneeNumber'    => $order['shipping']['phone'] ?? '',
    //             'consigneeAddress'   => $order['shipping']['address1'] ?? '',
    //             'consigneeEmail'     => null,

    //             'bookNo'             => $bookNo,
    //             'bookDate'           => now()->toDateString(),
    //         ]);

    //         $createdBookings[] = [
    //             'shopify_order' => $orderNo,
    //             'booking_no'    => $bookNo
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'bookings_created' => $createdBookings
    //     ]);
    // }

    // public function pushOrders(Request $request)
    // {
    //     $authHeader = $request->header('Authorization');

    //     if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $appToken = str_replace('Bearer ', '', $authHeader);

    //     $user = User::where('app_token', $appToken)->first();

    //     if (!$user) {
    //         return response()->json(['error' => 'Invalid app token'], 401);
    //     }

    //     $customerId = $user->id;
    //     $createdBookings = [];

    //     foreach ($request->orders as $order) {

    //         if (empty($order['shipping'])) {
    //             continue;
    //         }

    //         $orderNo = ltrim($order['order_number'], '#');

    //         if (Booking::where('orderNo', $orderNo)->exists()) {
    //             continue;
    //         }

    //         // 🔥 Generate booking number SAME as store()
    //         $typeCode = '01';
    //         $prefix  = 'AB';
    //         $year    = date('y');
    //         $month   = date('m');
    //         $random  = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    //         $bookNo  = "{$prefix}{$year}{$month}{$typeCode}{$random}";

    //         $paymentMode = $order['financial_status'] === 'PAID'
    //             ? 'non_cod'
    //             : 'cod';

    //         $bookingData = [
    //             'customer_id'        => $customerId,
    //             'bookingType'        => 'domestic',
    //             'paymentMode'        => $paymentMode,
    //             'destination'        => $order['shipping']['city'] ?? '',
    //             'destinationCountry' => $order['shipping']['country'] ?? '',
    //             'invoiceValue'       => $order['amount'],
    //             'weight'             => 1,
    //             'pieces'             => 1,
    //             'orderNo'            => $orderNo,
    //             'consigneeName'      => $order['shipping']['name'] ?? '',
    //             'consigneeNumber'    => $order['shipping']['phone'] ?? '',
    //             'consigneeAddress'   => $order['shipping']['address1'] ?? '',
    //             'bookNo'             => $bookNo,
    //             'bookDate'           => now()->toDateString(),
    //         ];

    //         Booking::create($bookingData);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 🔥 TRANZO INTEGRATION (Same As store)
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($paymentMode === 'cod') {

    //             $tranzoPayload = [
    //                 'reference_number' => $bookNo,
    //                 'customer_name'    => $order['shipping']['name'] ?? '',
    //                 'customer_phone'   => $order['shipping']['phone'] ?? '',
    //                 'destination_city' => $order['shipping']['city'] ?? '',
    //                 'delivery_address' => $order['shipping']['address1'] ?? '',
    //                 'cod_amount'       => (int)$order['amount'],
    //                 'booking_weight'   => 1,
    //                 'total_items'      => 1,
    //             ];

    //             $tranzoResponse = Http::withHeaders([
    //                 'Accept'       => 'application/json',
    //                 'Content-Type' => 'application/json',
    //                 'api-token'    => '09f4924c715a474385938f7fef946e04',
    //             ])->post(
    //                 'https://api-integration.tranzo.pk/api/custom/v1/create-order/',
    //                 $tranzoPayload
    //             );

    //             $tranzoData = $tranzoResponse->json();

    //             if (isset($tranzoData['tracking_number'])) {
    //                 \App\Models\ThirdPartyBooking::create([
    //                     'book_no'      => $bookNo,
    //                     'book_date'    => now()->toDateString(),
    //                     'company_name' => 'Tranzo',
    //                     'ref_no'       => $tranzoData['tracking_number'],
    //                     'remarks'      => 'Auto booked via Shopify Push',
    //                     'updated_by'   => null,
    //                 ]);
    //             }
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 🔥 SONIC INTEGRATION (Same As store)
    //         |--------------------------------------------------------------------------
    //         */

    //         // Same pattern as above — use $bookNo and shipping data

    //         $createdBookings[] = [
    //             'shopify_order' => $orderNo,
    //             'booking_no'    => $bookNo
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'bookings_created' => $createdBookings
    //     ]);
    // }

    // public function pushOrders(Request $request)
    // {
    //     \Log::info('PushOrders API called');

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 1️⃣ Authenticate Using app_token
    //     |--------------------------------------------------------------------------
    //     */

    //     $authHeader = $request->header('Authorization');

    //     if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    //         \Log::warning('Unauthorized request - Missing Bearer token');
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $appToken = str_replace('Bearer ', '', $authHeader);

    //     $user = User::where('app_token', $appToken)->first();

    //     if (!$user) {
    //         \Log::warning('Invalid app token used');
    //         return response()->json(['error' => 'Invalid app token'], 401);
    //     }

    //     $customerId = $user->id;
    //     $createdBookings = [];

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 2️⃣ Process Orders
    //     |--------------------------------------------------------------------------
    //     */

    //     foreach ($request->orders as $order) {

    //         try {

    //             if (empty($order['shipping'])) {
    //                 \Log::warning('Skipping order - No shipping address', $order);
    //                 continue;
    //             }

    //             $orderNo = ltrim($order['order_number'], '#');

    //             \Log::info('Processing Order', ['order_no' => $orderNo]);

    //             if (Booking::where('orderNo', $orderNo)->exists()) {
    //                 \Log::info('Skipping duplicate booking', ['order_no' => $orderNo]);
    //                 continue;
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 3️⃣ Generate Booking Number
    //             |--------------------------------------------------------------------------
    //             */

    //             $typeCode = '01';
    //             $prefix  = 'AB';
    //             $year    = date('y');
    //             $month   = date('m');
    //             $random  = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    //             $bookNo  = "{$prefix}{$year}{$month}{$typeCode}{$random}";

    //             $paymentMode = $order['financial_status'] === 'PAID'
    //                 ? 'non_cod'
    //                 : 'cod';

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 4️⃣ Create Booking
    //             |--------------------------------------------------------------------------
    //             */

    //             Booking::create([
    //                 'customer_id'        => $customerId,
    //                 'bookingType'        => 'domestic',
    //                 'paymentMode'        => $paymentMode,
    //                 'destination'        => $order['shipping']['city'] ?? '',
    //                 'destinationCountry' => $order['shipping']['country'] ?? '',
    //                 'invoiceValue'       => $order['amount'],
    //                 'weight'             => 1,
    //                 'pieces'             => 1,
    //                 'orderNo'            => $orderNo,
    //                 'consigneeName'      => $order['shipping']['name'] ?? '',
    //                 'consigneeNumber'    => $order['shipping']['phone'] ?? '',
    //                 'consigneeAddress'   => $order['shipping']['address1'] ?? '',
    //                 'bookNo'             => $bookNo,
    //                 'bookDate'           => now()->toDateString(),
    //             ]);

    //             \Log::info('Booking Created', ['book_no' => $bookNo]);

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 🔥 TRANZO INTEGRATION
    //             |--------------------------------------------------------------------------
    //             */

    //             if ($paymentMode === 'cod') {

    //                 $tranzoPayload = [
    //                     'reference_number' => $bookNo,
    //                     'customer_name'    => $order['shipping']['name'] ?? '',
    //                     'customer_phone'   => $order['shipping']['phone'] ?? '',
    //                     'destination_city' => $order['shipping']['city'] ?? '',
    //                     'delivery_address' => $order['shipping']['address1'] ?? '',
    //                     'cod_amount'       => (int)$order['amount'],
    //                     'booking_weight'   => 1,
    //                     'total_items'      => 1,
    //                 ];

    //                 \Log::info('Sending Tranzo Request', [
    //                     'book_no' => $bookNo,
    //                     'payload' => $tranzoPayload
    //                 ]);

    //                 $tranzoResponse = Http::withHeaders([
    //                     'Accept'       => 'application/json',
    //                     'Content-Type' => 'application/json',
    //                     'api-token'    => '09f4924c715a474385938f7fef946e04',
    //                 ])->post(
    //                     'https://api-integration.tranzo.pk/api/custom/v1/create-order/',
    //                     $tranzoPayload
    //                 );

    //                 $tranzoData = $tranzoResponse->json();

    //                 \Log::info('Tranzo Response', [
    //                     'book_no' => $bookNo,
    //                     'status'  => $tranzoResponse->status(),
    //                     'response'=> $tranzoData
    //                 ]);

    //                 if (isset($tranzoData['tracking_number'])) {

    //                     \App\Models\ThirdPartyBooking::create([
    //                         'book_no'      => $bookNo,
    //                         'book_date'    => now()->toDateString(),
    //                         'company_name' => 'Tranzo',
    //                         'ref_no'       => $tranzoData['tracking_number'],
    //                         'remarks'      => 'Auto booked via Shopify Push',
    //                         'updated_by'   => null,
    //                     ]);

    //                 } else {
    //                     \Log::error('Tranzo Booking Failed', [
    //                         'book_no' => $bookNo,
    //                         'response'=> $tranzoData
    //                     ]);
    //                 }

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | 🔥 SONIC INTEGRATION
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 // ⚠ You MUST map city to Sonic city ID properly
    //                 $consigneeCityId = 123; // Replace with dynamic mapping

    //                 if ($consigneeCityId > 0) {

    //                     $sonicPayload = [
    //                         'service_type_id'            => 1,
    //                         'pickup_address_id'          => 617025,
    //                         'information_display'        => 0,
    //                         'consignee_city_id'          => $consigneeCityId,
    //                         'consignee_name'             => $order['shipping']['name'] ?? '',
    //                         'consignee_address'          => $order['shipping']['address1'] ?? '',
    //                         'consignee_phone_number_1'   => $order['shipping']['phone'] ?? '',
    //                         'order_id'                   => $bookNo,
    //                         'item_product_type_id'       => 1,
    //                         'item_description'           => 'Shopify Order',
    //                         'item_quantity'              => 1,
    //                         'item_insurance'             => 0,
    //                         'item_price'                 => (int)$order['amount'],
    //                         'pickup_date'                => now()->toDateString(),
    //                         'special_instructions'       => '',
    //                         'estimated_weight'           => 1,
    //                         'shipping_mode_id'           => 1,
    //                         'amount'                     => (int)$order['amount'],
    //                         'parcel_value'               => (int)$order['amount'],
    //                         'payment_mode_id'            => 1,
    //                         'charges_mode_id'            => 2,
    //                         'open_box'                   => 0,
    //                         'pieces_quantity'            => 1,
    //                         'shipper_reference_number_1' => $bookNo,
    //                     ];

    //                     \Log::info('Sending Sonic Request', [
    //                         'book_no' => $bookNo,
    //                         'payload' => $sonicPayload
    //                     ]);

    //                     $sonicResponse = Http::withHeaders([
    //                         'Authorization' => 'YOUR_SONIC_API_KEY',
    //                         'Content-Type'  => 'application/json',
    //                         'Accept'        => 'application/json',
    //                     ])->post('https://sonic.pk/api/shipment/book', $sonicPayload);

    //                     $sonicData = $sonicResponse->json();

    //                     \Log::info('Sonic Response', [
    //                         'book_no' => $bookNo,
    //                         'status'  => $sonicResponse->status(),
    //                         'response'=> $sonicData
    //                     ]);

    //                     if (isset($sonicData['status']) && $sonicData['status'] == 0) {

    //                         \App\Models\ThirdPartyBooking::create([
    //                             'book_no'      => $bookNo,
    //                             'book_date'    => now()->toDateString(),
    //                             'company_name' => 'Sonic',
    //                             'ref_no'       => $sonicData['tracking_number'] ?? 'N/A',
    //                             'remarks'      => 'Auto booked via Shopify Push',
    //                             'updated_by'   => null,
    //                         ]);

    //                     } else {
    //                         \Log::error('Sonic Booking Failed', [
    //                             'book_no' => $bookNo,
    //                             'response'=> $sonicData
    //                         ]);
    //                     }
    //                 }
    //             }

    //             $createdBookings[] = [
    //                 'shopify_order' => $orderNo,
    //                 'booking_no'    => $bookNo
    //             ];

    //         } catch (\Exception $e) {

    //             \Log::error('Error Processing Order', [
    //                 'order' => $order,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'bookings_created' => $createdBookings
    //     ]);
    // }

    public function pushOrders(Request $request)
    {
        \Log::info('========== PushOrders API Called ==========');
        \Log::info('Incoming Payload', $request->all());

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Authenticate Using app_token
        |--------------------------------------------------------------------------
        */

        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            \Log::warning('Unauthorized - Missing Bearer token');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $appToken = str_replace('Bearer ', '', $authHeader);
        \Log::info('App Token Extracted');

        $user = User::where('app_token', $appToken)->first();

        if (!$user) {
            \Log::error('Invalid app token used');
            return response()->json(['error' => 'Invalid app token'], 401);
        }

        \Log::info('User authenticated', ['user_id' => $user->id]);

        $customerId = $user->id;
        $createdBookings = [];

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Fetch Tranzo Operational Cities ONCE
        |--------------------------------------------------------------------------
        */

        $tranzoCities = [];

        $tranzoResponse = Http::withHeaders([
            'api-token'    => '09f4924c715a474385938f7fef946e04',
            'Content-Type' => 'application/json',
        ])->get('https://api-integration.tranzo.pk/api/custom/v1/get-operational-cities/');

        if ($tranzoResponse->successful()) {
            $tranzoCities = collect($tranzoResponse->json())
                ->pluck('city_name')
                ->map(fn($city) => strtolower(trim($city)))
                ->toArray();

            \Log::info('Tranzo Cities Loaded', ['count' => count($tranzoCities)]);
        } else {
            \Log::error('Failed to fetch Tranzo cities');
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Process Orders
        |--------------------------------------------------------------------------
        */

        foreach ($request->orders as $order) {

            try {

                \Log::info('------------------------------');
                \Log::info('Processing Order', $order);

                if (empty($order['address']) || empty($order['city'])) {
                    \Log::warning('Skipping - Missing address or city');
                    continue;
                }

                $orderNo = ltrim($order['orderNumber'], '#');
                $cityName = strtolower(trim($order['city']));

                if (Booking::where('orderNo', $orderNo)->exists()) {
                    \Log::warning('Duplicate booking skipped', ['order_no' => $orderNo]);
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Generate Booking Number
                |--------------------------------------------------------------------------
                */

                $bookNo = 'AB' . date('y') . date('m') . '01' . str_pad(rand(0,9999),4,'0',STR_PAD_LEFT);

                $paymentMode = strtolower($order['financialStatus']) === 'paid'
                    ? 'non_cod'
                    : 'cod';

                /*
                |--------------------------------------------------------------------------
                | Create Booking
                |--------------------------------------------------------------------------
                */

                Booking::create([
                    'customer_id'        => $customerId,
                    'bookingType'        => 'domestic',
                    'paymentMode'        => $paymentMode,
                    'destination'        => $order['city'],
                    'destinationCountry' => 'Canada',
                    'invoiceValue'       => $order['cod'],
                    'weight'             => $order['kg'] ?? 1,
                    'pieces'             => 1,
                    'orderNo'            => $orderNo,
                    'consigneeName'      => $order['name'],
                    'consigneeNumber'    => $order['phone'],
                    'consigneeAddress'   => $order['address'],
                    'bookNo'             => $bookNo,
                    'bookDate'           => now()->toDateString(),
                ]);

                \Log::info('Booking Created Successfully', ['book_no' => $bookNo]);

                /*
                |--------------------------------------------------------------------------
                | 4️⃣ Courier Routing Logic
                |--------------------------------------------------------------------------
                */

                $orderfulfilled = false;
                if ($orderfulfilled == false) {

                    if ($cityName === 'karachi') {

                        \Log::info('City is Karachi → No courier will be used');
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | TRANZO (Priority)
                    |--------------------------------------------------------------------------
                    */

                    if (in_array($cityName, $tranzoCities)) {

                        \Log::info('City found in Tranzo list → Sending to Tranzo');

                        $tranzoPayload = [
                            'reference_number' => $bookNo,
                            'order_details'    => 'No Comment',
                            'customer_name'    => $order['name'],
                            'customer_phone'   => $order['phone'],
                            'destination_city' => $order['city'],
                            'delivery_address' => $order['address'],
                            'cod_amount'       => (int)$order['cod'],
                            'booking_weight'   => (float)($order['kg'] ?? 1),
                            'total_items'      => 1,
                            'ds_shipment_type'     => 1,
                            'store_id'             => 1,
                            'pickup_address_code'  => 'TMLO',
                            'return_address_code'  => 'TMLO',
                        ];

                        \Log::info('Tranzo Payload', $tranzoPayload);

                        $tranzoBook = Http::withHeaders([
                            'Accept'       => 'application/json',
                            'Content-Type' => 'application/json',
                            'api-token'    => '09f4924c715a474385938f7fef946e04',
                        ])->post(
                            'https://api-integration.tranzo.pk/api/custom/v1/create-order/',
                            $tranzoPayload
                        );

                        $tranzoData = $tranzoBook->json();

                        \Log::info('Tranzo Response', [
                            'status' => $tranzoBook->status(),
                            'body'   => $tranzoData
                        ]);

                        if (!empty($tranzoData['tracking_number'])) {

                            ThirdPartyBooking::create([
                                'book_no'      => $bookNo,
                                'book_date'    => now()->toDateString(),
                                'company_name' => 'Tranzo',
                                'ref_no'       => $tranzoData['tracking_number'],
                                'remarks'      => 'Auto booked via Shopify Push',
                                'updated_by'   => null,
                            ]);

                            \Log::info('Tranzo ThirdPartyBooking Saved');

                        } else {
                            \Log::error('Tranzo Booking Failed');
                        }

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | SONIC
                        |--------------------------------------------------------------------------
                        */

                        \Log::info('City not in Tranzo → Sending to Sonic');

                        $sonicResponse = Http::withHeaders([
                            'Authorization' => 'aWNSR1VFYjBwcnhvRmp2T1RqRWpmOE9nMVNHNGdMVkc5aGp4VEdub29KYnF5WTdFajhKSHhrQ3Nlc214698b61c3af9b9',
                            'Content-Type'  => 'application/json',
                            'Accept'        => 'application/json',
                        ])->get('https://sonic.pk/api/cities');

                        $sonicCityId = 0;

                        if ($sonicResponse->successful()) {

                            $cities = $sonicResponse->json()['cities'] ?? $sonicResponse->json();

                            foreach ($cities as $city) {
                                $name = strtolower(trim($city['name'] ?? $city['city_name'] ?? ''));
                                if ($name === $cityName) {
                                    $sonicCityId = $city['id'] ?? $city['city_id'];
                                    break;
                                }
                            }
                        }

                        if ($sonicCityId > 0) {

                            $sonicPayload = [
                                'service_type_id'            => 1,
                                'amount'                     => (int)$order['cod'] ?? 0,
                                'parcel_value'               => (int)$order['cod'] ?? 0,
                                'pickup_address_id'          => 617025,
                                'information_display'        => 0,
                                'consignee_city_id'          => $sonicCityId,
                                'consignee_name'             => $order['name'],
                                'consignee_address'          => $order['address'],
                                'consignee_phone_number_1'   => $order['phone'],
                                'order_id'                   => $bookNo,
                                'item_product_type_id'       => 1,
                                'item_product_type_id'       => "No Comment",
                                'item_quantity'              => 1,
                                'item_insurance'             => 0,
                                'item_price'                 => (int)$order['cod'],
                                'pickup_date'                => now()->toDateString(),
                                'estimated_weight'           => (float)($order['kg'] ?? 1),
                                'payment_mode_id'            => 1,
                                'charges_mode_id'            => 2,
                                'open_box'                   => 0,
                                'shipping_mode_id'           => 1,
                                'shipper_reference_number_1' => $bookNo,
                            ];

                            \Log::info('Sonic Payload', $sonicPayload);

                            $sonicBook = Http::withHeaders([
                                'Authorization' => 'aWNSR1VFYjBwcnhvRmp2T1RqRWpmOE9nMVNHNGdMVkc5aGp4VEdub29KYnF5WTdFajhKSHhrQ3Nlc214698b61c3af9b9',
                                'Content-Type'  => 'application/json',
                                'Accept'        => 'application/json',
                            ])->post('https://sonic.pk/api/shipment/book', $sonicPayload);

                            $sonicData = $sonicBook->json();

                            \Log::info('Sonic Response', [
                                'status' => $sonicBook->status(),
                                'body'   => $sonicData
                            ]);

                            if (isset($sonicData['status']) && $sonicData['status'] == 0) {

                                ThirdPartyBooking::create([
                                    'book_no'      => $bookNo,
                                    'book_date'    => now()->toDateString(),
                                    'company_name' => 'Sonic',
                                    'ref_no'       => $sonicData['tracking_number'] ?? 'N/A',
                                    'remarks'      => 'Auto booked via Shopify Push',
                                    'updated_by'   => null,
                                ]);

                                \Log::info('Sonic ThirdPartyBooking Saved');

                            } else {
                                \Log::error('Sonic Booking Failed');
                            }

                        } else {
                            \Log::error('Sonic City ID Not Found');
                        }
                    }
                }

                $createdBookings[] = [
                    'shopify_order' => $orderNo,
                    'booking_no'    => $bookNo
                ];

            } catch (\Exception $e) {

                \Log::error('Order Processing Exception', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        \Log::info('========== PushOrders Completed ==========');

        return response()->json([
            'success' => true,
            'bookings_created' => $createdBookings
        ]);
    }

    
}