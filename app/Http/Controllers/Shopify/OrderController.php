<?php

namespace App\Http\Controllers\Shopify;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

    public function getOrders(Request $request)
    {
        // 🔐 Step 1 — Verify Shopify session token
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['error' => 'Missing session token'], 401);
        }

        $jwt = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode(
                $jwt,
                new Key(config('services.shopify.secret'), 'HS256')
            );

            $shop = str_replace('https://', '', $decoded->dest);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid session token'], 401);
        }

        // 🔎 Step 2 — Get user with this shop
        $user = User::where('shop_domain', $shop)->first();

        if (!$user || !$user->shopify_access_token) {
            return response()->json([
                'error' => 'Store not connected'
            ], 400);
        }

        // 📦 Step 3 — Fetch Orders from Shopify
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $user->shopify_access_token,
            'Content-Type' => 'application/json'
        ])->post("https://{$shop}/admin/api/2024-01/graphql.json", [
            'query' => '
            {
              orders(first: 20, sortKey: CREATED_AT, reverse: true) {
                edges {
                  node {
                    id
                    name
                    createdAt
                    displayFinancialStatus
                    displayFulfillmentStatus
                    totalPriceSet {
                      shopMoney {
                        amount
                        currencyCode
                      }
                    }
                  }
                }
              }
            }
            '
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to fetch orders',
                'details' => $response->body()
            ], 500);
        }

        return response()->json($response->json());
    }


    public function pushOrders(Request $request)
    {
        $user = $request->get('auth_user');

        $orders = $request->orders;

        foreach ($orders as $order) {
            // Send to courier API here
        }

        return response()->json(['success' => true]);
    }

    
}