<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\User;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Partner;
use App\Models\Customer;
use App\Models\VoidBooking;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BookingStatus;
use App\Mail\AssignPartnerMail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BookingAttachment;
use App\Models\ThirdPartyBooking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Milon\Barcode\Facades\DNS2DFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Store a new booking (works for all types)
     */
    public function store(Request $request)
    {
        // 1️⃣ Validate input
        $validated = $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'bookingType'          => 'required|in:domestic,export,import,cross_border',
            'service'              => 'nullable|in:overnight,overnet',
            'bookChannel'          => 'nullable|in:facebook,whatsapp,instagram,others',
            'paymentMode'          => 'nullable|in:cod,non_cod',
            'origin'               => 'nullable',
            'originCountry'        => 'nullable',
            'destination'          => 'nullable',
            'destinationCountry'   => 'nullable',
            'postalCode'           => 'nullable',
            'invoiceValue'         => 'nullable|numeric',
            'weight'               => 'nullable|numeric',
            'pieces'               => 'nullable|numeric',
            'length'               => 'nullable|numeric',
            'width'                => 'nullable|numeric',
            'height'               => 'nullable|numeric',
            'dimensionalWeight'    => 'nullable|numeric',
            'orderNo'              => 'nullable',
            'arrivalClearance'     => 'nullable|string',
            'itemContent'          => 'nullable',
            'itemDetail'           => 'nullable',
            'shipperCompany'       => 'nullable',
            'shipperName'          => 'nullable',
            'shipperNumber'        => 'nullable',
            'shipperEmail'         => 'nullable|email',
            'shipperAddress'       => 'nullable',
            'consigneeCompany'     => 'nullable',
            'consigneeName'        => 'nullable',
            'consigneeNumber'      => 'nullable',
            'consigneeEmail'       => 'nullable|email',
            'consigneeAddress'     => 'nullable',
            'remarks'              => 'nullable',
            'pickupInstructions'   => 'nullable',
            'deliveryInstructions' => 'nullable',
            'codAmount'            => 'nullable|numeric',
            'salesPerson'          => 'nullable|exists:users,id',
            'territory'            => 'nullable|exists:users,id',
            'rateType'             => 'nullable',
            'consignee_city_id'    => 'nullable', // Sonic city id
        ]);

        if (isset($validated['arrivalClearance'])) {
            $mapping = [
                'dr'               => 'DR',
                'console'          => 'Console',
                'actual clearance' => 'Actual Clearance',
            ];
            $key = strtolower(trim($validated['arrivalClearance']));
            $validated['arrivalClearance'] = $mapping[$key] ?? $validated['arrivalClearance'];
        }

        // 2️⃣ Determine type code
        switch ($validated['bookingType']) {
            case 'domestic':
                $typeCode = '01';
                break;
            case 'import':
                $typeCode = '02';
                break;
            case 'export':
                $typeCode = '03';
                break;
            case 'cross_border':
                $typeCode = '04';
                break;
            default:
                $typeCode = '00';
                break;
        }

        // 3️⃣ Generate booking number
        $prefix  = 'AB';
        $year    = date('y');
        $month   = date('m');
        $random  = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $bookNo  = "{$prefix}{$year}{$month}{$typeCode}{$random}";

        // 4️⃣ Auto values
        $validated['bookNo']      = $bookNo;
        $validated['bookDate']    = now()->toDateString();
        $validated['salesPerson'] = $request->input('territory', null);

        // 5️⃣ Customer data fetch karo Sonic API ke liye
        $customer = \App\Models\Customer::find($validated['customer_id']);

        // 6️⃣ Save to DB
        // 6️⃣ Save to DB
        Booking::create($validated);

        $destinationSource = $request->input('destination_source');
        $consigneeCityId   = (int) $request->input('consignee_city_id', 0);
        $sonicMessage      = ''; // Fix: variable define karo

        if ($destinationSource === 'sonic' && $consigneeCityId > 0 && ($validated['paymentMode'] ?? '') === 'cod') {

            $sonicPayload = [
                'service_type_id'            => 1,
                'pickup_address_id'          => 617025,
                'information_display'        => 0,
                'consignee_city_id'          => $consigneeCityId,
                'consignee_name'             => $customer->customer_name ?? '',
                'consignee_address'          => $customer->address_1 ?? '',
                'consignee_phone_number_1'   => $customer->contact_no_1 ?? '',
                'order_id'                   => $bookNo,
                'item_product_type_id'       => 1,
                'item_description'           => $validated['itemDetail'] ?? '',
                'item_quantity'              => (int)($validated['pieces'] ?? 1),
                'item_insurance'             => 0,
                'item_price'                 => (int)($validated['codAmount'] ?? 0),
                'pickup_date'                => $validated['bookDate'],
                'special_instructions'       => $validated['remarks'] ?? '',
                'estimated_weight'           => (float)($validated['weight'] ?? 0),
                'shipping_mode_id'           => 1,
                'amount'                     => (int)($validated['codAmount'] ?? 0),
                'parcel_value'               => (int)($validated['invoiceValue'] ?? 0),
                'payment_mode_id'            => 1,
                'charges_mode_id'            => 2,
                'open_box'                   => 0,
                'pieces_quantity'            => min(max((int)($validated['pieces'] ?? 1), 1), 10),
                'shipper_reference_number_1' => $bookNo,
            ];

            if (!empty($customer->contact_no_2)) {
                $sonicPayload['consignee_phone_number_2'] = $customer->contact_no_2;
            }
            if (!empty($customer->email_1)) {
                $sonicPayload['consignee_email_address'] = $customer->email_1;
            }
            $sonicResponse = Http::withHeaders([
                'Authorization' => 'aWNSR1VFYjBwcnhvRmp2T1RqRWpmOE9nMVNHNGdMVkc5aGp4VEdub29KYnF5WTdFajhKSHhrQ3Nlc214698b61c3af9b9',
                'Content-Type'  => 'application/json',
            ])->post('https://sonic.pk/api/shipment/book', $sonicPayload);

            $sonicData = $sonicResponse->json();

            // ✅ Log add kiya
            \Log::info('Sonic API Response', [
                'status_code' => $sonicResponse->status(),
                'payload'     => $sonicPayload,
                'response'    => $sonicData,
            ]);

            if (isset($sonicData['status']) && $sonicData['status'] == 0) {
                $trackingNo   = $sonicData['tracking_number'] ?? 'N/A';
                $sonicMessage = " | Sonic Tracking: {$trackingNo}";
            } else {
                \Log::error('Sonic API Error', ['payload' => $sonicPayload, 'response' => $sonicData]);
                $sonicMessage = ' | Sonic Error: ' . ($sonicData['message'] ?? 'Unknown');
            }
        }

        return redirect()->back()->with(
            'success',
            ucfirst($validated['bookingType']) . " booking created successfully. Booking No: {$bookNo}" . $sonicMessage
        );
    }

    /**
     * Track booking
     */

    // public function getBookingByBookNo(Request $request)
    // {
    //     $booking = Booking::where('bookNo', $request->bookNo)->first();

    //     if (!$booking) {
    //         return redirect()->back()->with('error', 'Booking not found');
    //     }

    //     return view('tracking', compact('booking'));
    // }
    public function getBookingByBookNo(Request $request)
    {
        $bookNosInput = $request->input('book_no');

        // 🔹 Split comma separated numbers
        $bookNos = array_filter(array_map('trim', explode(',', $bookNosInput)));

        // 🔹 Get bookings with relations
        $bookings = Booking::with(['statuses.user', 'customer'])
            ->whereIn('bookNo', $bookNos)
            ->get();

        // 🔹 Get 3PL reference numbers
        $thirdPartyBookings = ThirdPartyBooking::whereIn('book_no', $bookNos)
            ->pluck('ref_no', 'book_no')
            ->toArray();

        // 🔹 Other data (agar kahin use ho)
        $users = User::all();
        $customers = Customer::all();
        $countries = Country::all();

        $pakistan = Country::where('name', 'Pakistan')->first();

        if ($pakistan) {
            $cities = City::where('country_id', $pakistan->id)->get();
        } else {
            $cities = collect();
        }

        // ✅ IMPORTANT — CORRECT VARIABLES PASS KARO
        return view('book-tracking', compact(
            'bookings',
            'thirdPartyBookings',
            'users',
            'customers',
            'countries',
            'cities'
        ));
    }


    /**
     * Booking status page
     */
    public function bookingStatus()
    {
        $bookings = Booking::with('customer')->latest()->paginate(50);
        return view('booking-status', compact('bookings'));
    }

    // public function createDomestic()
    // {
    //     $customers = Customer::all();
    //     $cities    = City::all();
    //     $users     = User::all();
    //     return view('domestic-booking', compact('customers', 'cities', 'users'))
    //         ->with('bookingType', 'domestic');
    // }

    // public function createExport()
    // {
    //     $customers = Customer::all();
    //     $cities    = City::all();
    //     $users     = User::all();
    //     $countries  = Country::all();
    //     return view('export-booking', compact('customers', 'cities', 'users', 'countries'))
    //         ->with('bookingType', 'export');
    // }

    // public function createImport()
    // {
    //     $customers = Customer::all();
    //     $cities    = City::all();
    //     $users     = User::all();
    //     $countries  = Country::all();
    //     return view('import-booking', compact('customers', 'cities', 'users', 'countries'))
    //         ->with('bookingType', 'import'); // ✅ fix here
    // }

    // public function createCrossBorder()
    // {
    //     $customers = Customer::all();
    //     $cities    = City::all();
    //     $users     = User::all();
    //     return view('crossborder-booking', compact('customers', 'cities', 'users'))
    //         ->with('bookingType', 'cross_border');
    // }

    public function createDomestic()
    {
        $customers = Customer::all();
        $users     = User::all();

        // Existing Tranzo API
        $tranzoCities = [];
        $tranzoResponse = Http::withHeaders([
            'api-token'    => '09f4924c715a474385938f7fef946e04',
            'Content-Type' => 'application/json',
        ])->get('https://api-integration.tranzo.pk/api/custom/v1/get-operational-cities/');

        if ($tranzoResponse->successful()) {
            $tranzoCities = $tranzoResponse->json();
        }

        // New Sonic API
        $sonicCities = [];
        $sonicResponse = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'aWNSR1VFYjBwcnhvRmp2T1RqRWpmOE9nMVNHNGdMVkc5aGp4VEdub29KYnF5WTdFajhKSHhrQ3Nlc214698b61c3af9b9',
        ])->get('https://sonic.pk/api/cities');

        if ($sonicResponse->successful()) {
            $sonicData = $sonicResponse->json();
            \Log::info('Sonic Cities Raw Response', ['data' => array_slice($sonicData, 0, 3)]); // pehle 3 records log karo
            $rawSonic  = isset($sonicData['cities']) ? $sonicData['cities'] : $sonicData;

            foreach ($rawSonic as $city) {
                $cityName = $city['name'] ?? $city['city_name'] ?? null;
                $cityId   = $city['id'] ?? $city['city_id'] ?? '';
                if ($cityName) {
                    $sonicCities[] = [
                        'city_name' => $cityName,
                        'id'        => $cityId,
                        'source'    => 'sonic',
                    ];
                }
            }
        }

        // Tranzo cities mein source add karein
        $tranzoCitiesTagged = array_map(function ($city) {
            $city['source'] = 'tranzo';
            return $city;
        }, $tranzoCities);

        // Dono merge karein - unique city names
        $allCities     = $tranzoCitiesTagged;
        $existingNames = array_column($tranzoCitiesTagged, 'city_name');

        foreach ($sonicCities as $sonicCity) {
            if (!in_array($sonicCity['city_name'], $existingNames)) {
                $allCities[] = $sonicCity;
            }
        }

        $cities = $allCities;

        return view('domestic-booking', compact('customers', 'users', 'cities', 'tranzoCities', 'sonicCities'))
            ->with('bookingType', 'domestic');
    }


    public function createExport()
    {
        $customers = Customer::all();
        $cities    = City::all();
        $users     = User::all();
        $countries  = Country::all();
        return view('export-booking', compact('customers', 'cities', 'users', 'countries'))
            ->with('bookingType', 'export');
    }

    public function createImport()
    {
        $customers = Customer::all();
        $cities    = City::all();
        $users     = User::all();
        $countries  = Country::all();
        return view('import-booking', compact('customers', 'cities', 'users', 'countries'))
            ->with('bookingType', 'import'); // ✅ fix here
    }

    public function createCrossBorder()
    {
        $customers = Customer::all();
        $cities    = City::all();       // optional if you need local cities
        $users     = User::all();
        $countries = Country::all();    // ✅ add this

        return view('crossborder-booking', compact('customers', 'cities', 'users', 'countries'))
            ->with('bookingType', 'cross_border');
    }


    public function pendingShipments()
    {
        $pendingShipments = Booking::with(['customer', 'statuses' => function ($q) {
            $q->latest();
        }])
            ->whereDoesntHave('statuses', function ($q) {
                $q->whereIn('status', ['DLV', 'Delivered', 'Returned']);  // ✅ exclude all delivered/returned
            })
            ->whereNotIn('id', function ($q) {                     // ✅ exclude void bookings
                $q->select('booking_id')->from('void_bookings');
            })
            ->paginate(50);

        $pendingShipments->onEachSide(1);

        $pendingShipments->getCollection()->each(function ($b) {
            $latestStatus = $b->latestStatusMixed();
            $b->latestStatusLabel = $latestStatus?->description ?? $latestStatus?->status ?? 'Pending';
            $b->latestStatusDateTime = $latestStatus?->created_at?->format('d-M-Y H:i') ?? '-';
        });

        $columns = [
            'Book No.',
            'Book Date',
            'Customer',
            'Product',
            'Origin',
            'Destination',
            'Shipper Name',
            'Shipper Contact No.',
            'Shipper Address',
            'Consignee Name',
            'Consignee Contact No.',
            'Weight (KG)',
            'Pieces',
            'Service',
            'Item Content',
            'Latest Status',
            'Status Updated At'
        ];

        return view('pending-shipments', compact('pendingShipments', 'columns'));
    }


    public function preview($id)
    {
        $booking = Booking::with(['customer', 'statuses'])->findOrFail($id);

        // Generate QR code as PNG file in temp folder
        $fileName = 'qr_' . $booking->id . '_' . Str::random(5) . '.png';
        $filePath = public_path('temp/' . $fileName);
        File::ensureDirectoryExists(public_path('temp'));

        // Generate QR code using DNS2D
        $qrData = DNS2DFacade::getBarcodePNG($booking->bookNo, 'QRCODE');
        File::put($filePath, base64_decode($qrData));

        $booking->qr_path = $filePath;

        // Generate PDF
        $pdf = Pdf::loadView('booking-preview', compact('booking'));

        // Open PDF in browser
        return $pdf->stream("Booking-{$booking->bookNo}.pdf");
    }


    public function editByBookNo($bookNo)
    {
        $booking = Booking::where('bookNo', $bookNo)->firstOrFail();

        // Redirect to correct edit form based on bookingType
        switch ($booking->bookingType) {
            case 'domestic':
                return redirect()->route('booking.edit.domestic', $booking->id);
            case 'export':
                return redirect()->route('booking.edit.export', $booking->id);
            case 'import':
                return redirect()->route('booking.edit.import', $booking->id);
            case 'cross_border':
                return redirect()->route('booking.edit.crossborder', $booking->id);
            default:
                return redirect()->back()->with('error', 'Invalid booking type.');
        }
    }


    public function index(Request $request)
    {
        $query = Booking::with(['customer', 'statuses' => function ($q) {
            $q->latest(); // get latest status
        }]);

        if ($request->filled('search')) {
            $query->where('bookNo', 'like', '%' . $request->search . '%');
        }

        $bookings = $query->latest()->paginate(50);

        // ✅ Map status codes
        $statusLabels = [
            'AIR' => 'Address Information Required',
            'ARV' => 'Arrived at BOX Facility',
            'ACW' => 'Arrived at China Warehouse',
            'AHF' => 'Arrived at Head Office',
            'AW'  => 'Arrived at Warehouse',
            'ACP' => 'Awaiting Customer Pickup',
            'COA' => 'Customer not Available',
            'DLV' => 'Delivered',
            'DFO' => 'Departed from Origin',
            'FM'  => 'Flight Manifested',
            'ICC' => 'In Custom Clearance',
            'ITD' => 'In Transit to Destination',
            'OFD' => 'Out For Delivery',
            'PS'  => 'Pickup Scheduled',
            'PCL' => 'Pickup from Customer',
            'CP'  => 'Pickup has been Assigned',
            'PA'  => 'Pickup has been Assigned',
            'RDR' => 'Rescheduled Delivery Requested',
            'RTS' => 'Return to Shipper',
            'TPL' => 'Shipment Forwarded',
            'SHP' => 'Shipment has been Picked',
        ];
        // ✅ Prepare Grid Data for Blade
        $gridData = $bookings->map(function ($b) use ($statusLabels) {
            $latestStatus = $b->statuses->sortByDesc('created_at')->first();
            $statusCode = $latestStatus?->status;
            $statusLabel = $statusLabels[$statusCode] ?? $statusCode ?? 'Pending';

            return [
                'bookNo'             => $b->bookNo,
                'bookDate'           => $b->bookDate,
                'statusTime'         => $latestStatus?->created_at?->format('d-M-Y H:i:s') ?? '-',
                'statusLabel'        => $statusLabel,
                'customer'           => $b->customer->customer_name ?? '-',
                'product'            => $b->bookingType ?? '-',
                'service'            => $b->service ?? '-',
                'itemContent'        => $b->itemContent ?? '-',
                'origin'             => $b->origin ?? '-',
                'destination'        => $b->destination ?? '-',
                'weight'             => $b->weight ?? '0',
                'pieces'             => $b->pieces ?? '0',
                'orderNo'            => $b->orderNo ?? '-',
                'shipperName'        => $b->shipperName ?? '-',
                'shipperNumber'      => $b->shipperNumber ?? '-',
                'shipperAddress'     => $b->shipperAddress ?? '-',
                'consigneeName'      => $b->consigneeName ?? '-',
                'consigneeNumber'    => $b->consigneeNumber ?? '-',
                'consigneeAddress'   => $b->consigneeAddress ?? '-',
            ];
        });
        // dd($gridData);

        return view('edit-booking', compact('bookings', 'statusLabels', 'gridData'));
    }

    public function editDomestic($id)
    {
        $booking = Booking::with('customer')->findOrFail($id);
        $customers = Customer::all();
        $users = User::all();

        // Get country id for Pakistan
        $pakistan = Country::where('name', 'Pakistan')->first();

        if ($pakistan) {
            $cities = City::where('country_id', $pakistan->id)->get();
        } else {
            $cities = collect(); // empty collection if Pakistan not found
        }

        return view('domestic-booking', compact('booking', 'customers', 'cities', 'users'))
            ->with('bookingType', 'domestic');
    }


    public function editExport($id)
    {
        $booking = Booking::with('customer')->findOrFail($id);
        $customers = Customer::all();
        $cities = City::all();
        $users = User::all();
        $countries = Country::all();
        return view('export-booking', compact('booking', 'customers', 'cities', 'users', 'countries'))
            ->with('bookingType', 'export');
    }

    public function editImport($id)
    {
        $booking = Booking::with('customer')->findOrFail($id);
        $customers = Customer::all();
        $cities = City::all();
        $users = User::all();
        $countries = Country::all();
        return view('import-booking', compact('booking', 'customers', 'cities', 'users', 'countries'))
            ->with('bookingType', 'import');
    }

    public function editCrossBorder($id)
    {
        $booking = Booking::with('customer')->findOrFail($id);
        $customers = Customer::all();
        $cities = City::all();
        $users = User::all();
        $countries = Country::all(); // <--- Add this line

        return view('crossborder-booking', compact('booking', 'customers', 'cities', 'users', 'countries'))
            ->with('bookingType', 'cross_border');
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // Validate request
        $validated = $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'service'             => 'nullable|in:document,express',
            'bookChannel'         => 'nullable|in:facebook,whatsapp,instagram,others',
            'paymentMode'         => 'nullable|in:cod,non_cod',
            'origin'              => 'nullable',
            'originCountry'       => 'nullable',
            'destination'         => 'nullable',
            'destinationCountry'  => 'nullable',
            'postalCode'          => 'nullable',
            'invoiceValue'        => 'nullable|numeric',
            'weight'              => 'nullable|numeric',
            'pieces'              => 'nullable|numeric',
            'length'              => 'nullable|numeric',
            'width'               => 'nullable|numeric',
            'height'              => 'nullable|numeric',
            'dimensionalWeight'   => 'nullable|numeric',
            'orderNo'             => 'nullable',
            'arrivalClearance' => 'nullable|in:DR,Console,Actual Clearance',
            'itemContent'         => 'nullable',
            'itemDetail'          => 'nullable',
            'shipperCompany'      => 'nullable',
            'shipperName'         => 'nullable',
            'shipperNumber'       => 'nullable',
            'shipperEmail'        => 'nullable|email',
            'shipperAddress'      => 'nullable',
            'consigneeCompany'    => 'nullable',
            'consigneeName'       => 'nullable',
            'consigneeNumber'     => 'nullable',
            'consigneeEmail'      => 'nullable|email',
            'consigneeAddress'    => 'nullable',
            'remarks'             => 'nullable',
            'pickupInstructions'  => 'nullable',
            'deliveryInstructions' => 'nullable',
            'codAmount'           => 'nullable|numeric',
            'salesPerson'         => 'nullable|exists:users,id',
            'territory'           => 'nullable|exists:users,id',
            'rateType'            => 'nullable',
        ]);

        $validated['salesPerson'] = $request->input('territory', null); // ✅ fixed
        $booking->update($validated);

        return redirect()->back()->with('success', 'Booking updated successfully.');
    }

    public function resetVoid($id)
    {
        $voidBooking = VoidBooking::findOrFail($id); // find the voided record

        $bookingNo = $voidBooking->booking->bookNo ?? 'N/A';
        $voidBooking->delete(); // remove the voided status

        session()->flash('success', "Booking {$bookingNo} has been restored from VOID bookings.");

        return redirect()->back();
    }


    public function voidedBookings()
    {
        // Get all voided booking IDs
        $voidBookingIds = VoidBooking::pluck('booking_id')->toArray();

        // Get bookings that are NOT voided
        $bookings = Booking::with('customer')
            ->whereNotIn('id', $voidBookingIds)
            ->latest()
            ->paginate(50);

        return view('booking-void-listing', compact('bookings'));
    }
    public function voidBookingsView()
    {
        // Get all VOID bookings with user and customer info
        $voidBookings = VoidBooking::with('booking.customer', 'user')
            ->latest()
            ->paginate(50);

        return view('void_bookings', compact('voidBookings'));
    }
    public function submitVoid(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'void_remarks' => 'required|string',
        ]);

        $voidBooking = VoidBooking::create([
            'booking_id' => $request->booking_id,
            'remarks'    => $request->void_remarks,
            'user_id'    => Auth::id(), // track who voided
        ]);

        $booking = $voidBooking->booking;

        // ✅ Add flash message
        session()->flash('success', "Booking {$booking->bookNo} has been added to VOID bookings.");

        return redirect()->back();
    }

    public function analysis(Request $request)
    {
        $fromDate = $request->fromDate;
        $toDate   = $request->toDate;

        // Common filter data (always pass)
        $origins      = Booking::select('origin')->distinct()->pluck('origin');
        $destinations = Booking::select('destination')->distinct()->pluck('destination');
        $customers    = Customer::all();
        $territories  = User::all();
        $products     = Booking::select('itemContent')->whereNotNull('itemContent')->distinct()->pluck('itemContent');

        // If no filters, render empty view
        if (!$fromDate || !$toDate) {
            return view('booking-analysis', [
                'filtersApplied' => false,
                'fromDate' => null,
                'toDate' => null,
                'origins' => $origins,
                'destinations' => $destinations,
                'customers' => $customers,
                'territories' => $territories,
                'products' => $products,
            ]);
        }

        // Filtered query
        $query = Booking::with('customer')
            ->whereBetween('bookDate', [$fromDate, $toDate]);

        if ($request->origin) {
            $query->where('origin', $request->origin);
        }

        if ($request->destination) {
            $query->where('destination', $request->destination);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->territory) {
            $query->where('territory', $request->territory);
        }

        if ($request->product) {
            $query->where('itemContent', $request->product);
        }

        $bookings = $query->get();

        // Product-wise aggregates
        $importCount = $bookings->where('bookingType', 'import')->count();
        $exportCount = $bookings->where('bookingType', 'export')->count();

        $productWise = [
            'import' => $importCount,
            'export' => $exportCount,
        ];

        // Weight-wise aggregates
        $weightWise = [
            'import' => $bookings->where('bookingType', 'import')->sum(fn($b) => floatval($b->weight ?? 0)),
            'export' => $bookings->where('bookingType', 'export')->sum(fn($b) => floatval($b->weight ?? 0)),
        ];

        // Daily booking trend
        $daily = $bookings
            ->filter(fn($b) => $b->bookDate) // Skip null bookDate
            ->groupBy(fn($b) => \Carbon\Carbon::parse($b->bookDate)->format('Y-m-d'));

        $dailyLabels = $daily->keys();
        $dailyCounts = $daily->map(fn($day) => $day->count())->values();

        return view('booking-analysis', [
            'filtersApplied' => true,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'productWise' => $productWise,
            'weightWise' => $weightWise,
            'dailyLabels' => $dailyLabels,
            'dailyCounts' => $dailyCounts,
            'origins' => $origins,
            'destinations' => $destinations,
            'customers' => $customers,
            'territories' => $territories,
            'products' => $products,
        ]);
    }

    // STEP 1: Show upload form
    public function showStep1()
    {
        session()->forget(['wizard_data', 'wizard_columns', 'wizard_mapping']);
        return view('booking-wizard', [
            'step' => 1,
        ]);
    }

    public function downloadSampleCsv()
    {
        $columns = [
            'item_detail',
            'destination',
            'weight',
            'pieces',
            'consignee_company',
            'consignee_name',
            'consignee_number',
            'consignee_email',
            'consignee_address',
            'destination_country',
            'postal_code',
            'invoice_value',
        ];

        $sampleData = [
            [
                'item_detail' => 'Laptop',
                'destination' => 'New York',
                'weight' => 2.5,
                'pieces' => 1,
                'consignee_company' => 'Tech Corp',
                'consignee_name' => 'John Doe',
                'consignee_number' => '1234567890',
                'consignee_email' => 'john@example.com',
                'consignee_address' => '123 Main St',
                'destination_country' => 'US',
                'postal_code' => '10001',
                'invoice_value' => '1200',
            ]
        ];

        $filename = "sample_bookings.csv";

        $handle = fopen('php://memory', 'w');
        fputcsv($handle, $columns);

        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($handle) {
            fpassthru($handle);
        }, 200, $headers);
    }
    // STEP 1: Handle CSV upload
    public function handleStep1(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows)); // get first row as header

        $data = [];
        foreach ($rows as $row) {
            $data[] = array_combine($header, array_pad($row, count($header), null));
        }

        session([
            'wizard_columns' => $header,
            'wizard_data' => $data,
        ]);

        return view('booking-wizard', [
            'step' => 2,
            'columns' => $header,
            'data' => $data,
            'targetColumns' => [
                'item_detail'         => 'varchar2(255)',
                'destination'         => 'varchar2(100)',
                'weight'              => 'number',
                'pieces'              => 'number',
                'consignee_company'   => 'varchar2(50)',
                'consignee_name'      => 'varchar2(50)',
                'consignee_number'    => 'varchar2(80)',
                'consignee_email'     => 'varchar2(50)',
                'consignee_address'   => 'varchar2(500)',
                'destination_country' => 'varchar2(10)',
                'postal_code'         => 'varchar2(20)',
                'invoice_value'       => 'varchar2(50)',
            ]
        ]);
    }

    // STEP 2: Handle column mapping
    public function handleStep2(Request $request)
    {
        $mapping = $request->input('mapping');

        if (!$mapping || !is_array($mapping)) {
            return redirect()->back()->with('error', 'Please map all required columns.');
        }

        session(['wizard_mapping' => $mapping]);

        return view('booking-wizard', [
            'step' => 3,
            'columns' => session('wizard_columns'),
            'data' => session('wizard_data'),
            'mapping' => $mapping,
            'customers' => Customer::all(),
        ]);
    }
    public function storeData(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $data     = session('wizard_data');
        $mapping  = session('wizard_mapping');

        if (!$data || !$mapping) {
            return redirect()->route('wizard.bookings.step1')->with('error', 'Session expired. Please start again.');
        }

        $success = 0;
        $fail = 0;
        $messages = [];

        foreach ($data as $index => $row) {
            try {
                Booking::create([
                    'bookNo'             => 'BK' . rand(100000, 999999),
                    'bookDate'           => now(),
                    'itemDetail'         => $row[$mapping['item_detail']] ?? null,
                    'destination'        => $row[$mapping['destination']] ?? null,
                    'weight'             => $row[$mapping['weight']] ?? null,
                    'pieces'             => $row[$mapping['pieces']] ?? null,
                    'consigneeCompany'   => $row[$mapping['consignee_company']] ?? null,
                    'consigneeName'      => $row[$mapping['consignee_name']] ?? null,
                    'consigneeNumber'    => $row[$mapping['consignee_number']] ?? null,
                    'consigneeEmail'     => $row[$mapping['consignee_email']] ?? null,
                    'consigneeAddress'   => $row[$mapping['consignee_address']] ?? null,
                    'destinationCountry' => $row[$mapping['destination_country']] ?? null,
                    'postalCode'         => $row[$mapping['postal_code']] ?? null,
                    'invoiceValue'       => $row[$mapping['invoice_value']] ?? null,
                    'customer_id'        => $request->customer_id,
                    'bookingType'        => 'import',
                ]);
                $success++;
            } catch (\Exception $e) {
                $fail++;
                $messages[] = "Row " . ($index + 2) . " failed: " . $e->getMessage();
            }
        }

        // clear session after upload
        session()->forget(['wizard_data', 'wizard_columns', 'wizard_mapping']);

        return redirect()->route('wizard.bookings.step1')
            ->with('success', "✅ {$success} bookings created, ❌ {$fail} failed.")
            ->with('messages', $messages);
    }

    public function searchData(Request $request)
    {
        $query = Booking::with('customer', 'salesPersonUser', 'territoryUser', 'latestStatus');

        // 🔹 Mapping of human-friendly columns to DB columns
        $columnsMap = [
            'Book No' => 'bookNo',
            'Book Date' => 'bookDate',
            'Customer' => 'customer_name',   // <- match Blade label
            'Product' => 'product',
            'Origin' => 'origin',
            'Destination' => 'destination',
            'Shipper Name' => 'shipperName',
            'Shipper Contact' => 'shipperNumber',
            'Shipper Address' => 'shipperAddress',
            'Consignee Name' => 'consigneeName',
            'Consignee Contact' => 'consigneeNumber',
        ];

        // 🔍 Global search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bookNo', 'like', "%$search%")
                    ->orWhereHas('customer', fn($q) => $q->where('customer_name', 'like', "%$search%"))
                    ->orWhere('product', 'like', "%$search%")
                    ->orWhere('origin', 'like', "%$search%")
                    ->orWhere('destination', 'like', "%$search%")
                    ->orWhere('shipperName', 'like', "%$search%")
                    ->orWhere('shipperNumber', 'like', "%$search%")
                    ->orWhere('shipperAddress', 'like', "%$search%")
                    ->orWhere('consigneeName', 'like', "%$search%")
                    ->orWhere('consigneeNumber', 'like', "%$search%");
            });
        }

        if ($request->filled('filter_column') && $request->filled('filter_operator') && $request->filled('filter_value')) {
            $col = $request->filter_column;
            $op = $request->filter_operator;
            $val = $request->filter_value;
            if ($op === 'like') $val = "%$val%";

            // Map human-friendly name to DB column
            $dbCol = $columnsMap[$col] ?? $col;

            // Default sort direction
            $dir = 'asc';

            if ($dbCol === 'customer_name') {
                $query->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
                    ->orderBy('customers.customer_name', $dir)
                    ->select('bookings.*');
            } else {
                $query->orderBy($dbCol, $dir);
            }
        }


        // 🔸 Sorting
        if ($request->has('sort_columns')) {
            foreach ($request->sort_columns as $sort) {
                $col = $sort['column'] ?? null;
                $dir = strtolower($sort['direction'] ?? 'asc');

                if ($col && in_array($dir, ['asc', 'desc'])) {
                    // Map human-friendly name to DB column
                    $dbCol = $columnsMap[$col] ?? $col;

                    // Handle related columns like customer_name
                    if ($dbCol === 'customer_name') {
                        $query->join('customers', 'bookings.customer_id', '=', 'customers.id')
                            ->orderBy('customers.customer_name', $dir)
                            ->select('bookings.*'); // avoid column ambiguity
                    } else {
                        $query->orderBy($dbCol, $dir);
                    }
                }
            }
        } else {
            $query->latest('id');
        }

        // 📊 Aggregate / Compute (session flash)
        $aggregateResult = session('aggregateResult');
        $computeResult = session('computeResult');

        // Pagination
        $bookings = $query->paginate(50)->appends($request->all());

        // Visible columns
        $visibleColumns = session('visible_columns', [
            'book_no',
            'book_date',
            'customer_name',
            'product',
            'origin',
            'destination',
            'shipperName',
            'shipperNumber',
            'shipperAddress',
            'consigneeName',
            'consigneeNumber'
        ]);

        return view('searchdata', compact(
            'bookings',
            'aggregateResult',
            'computeResult',
            'visibleColumns'
        ));
    }

    public function trackBooking($trackNumber)
    {
        $booking = Booking::with('statuses')->where('bookNo', $trackNumber)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Format response
        $response = [
            'trackNumber' => $booking->bookNo,
            'weight' => $booking->weight,
            'pieces' => $booking->pieces,
            'origin' => $booking->origin,
            'destination' => $booking->destination,
            'statusHistory' => $booking->statuses->map(function ($status) {
                return [
                    'date' => $status->created_at->format('d-M-Y'),
                    'time' => $status->created_at->format('H:i:s'),
                    'trackingStatus' => $status->status,
                    'description' => $status->description,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function downloadBookings(Request $request)
    {
        // Eager load customer relation
        $query = Booking::with('customer');

        // 🔍 Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bookNo', 'like', "%$search%")
                    ->orWhereHas('customer', fn($q) => $q->where('customer_name', 'like', "%$search%"))
                    ->orWhere('origin', 'like', "%$search%")
                    ->orWhere('destination', 'like', "%$search%")
                    ->orWhere('shipperName', 'like', "%$search%")
                    ->orWhere('consigneeName', 'like', "%$search%");
            });
        }

        // 🔸 Sorting (optional)
        if ($request->has('sort_columns')) {
            foreach ($request->sort_columns as $sort) {
                $col = $sort['column'] ?? null;
                $dir = strtolower($sort['direction'] ?? 'asc');
                if ($col && in_array($dir, ['asc', 'desc'])) {
                    $query->orderBy($col, $dir);
                }
            }
        }

        $bookings = $query->get();

        // 🔹 Headers - Match "Assigning Counter Partner" table
        $headers = [
            'Book No',
            'Company',
            'Customer',
            'Product',
            'Service',
            'Item Content',
            'Origin Country',
            'Origin',
            'Destination Country',
            'Destination',
            'Weight (KG)',
            'Pieces',
            'Order No',
            'Shipper Company Name',
            'Shipper Name',
            'Shipper Contact No',
            'Shipper Address',
            'Consignee Company Name',
            'Consignee Name',
            'Consignee Contact No',
            'Consignee Address'
        ];

        $filename = 'bookings_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($bookings, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($bookings as $b) {
                fputcsv($file, [
                    $b->bookNo,
                    'Airborn courier express', // Company
                    $b->customer->customer_name ?? '',
                    $b->customer->product ?? '', // Product from customer
                    $b->service ?? '',
                    $b->itemContent ?? '',
                    $b->originCountry ?? '',
                    $b->origin ?? '',
                    $b->destinationCountry ?? '',
                    $b->destination ?? '',
                    $b->weight ?? '',
                    $b->pieces ?? '',
                    $b->orderNo ?? '',
                    $b->shipperCompany ?? '',
                    $b->shipperName ?? '',
                    $b->shipperNumber ?? '',
                    $b->shipperAddress ?? '',
                    $b->consigneeCompany ?? '',
                    $b->consigneeName ?? '',
                    $b->consigneeNumber ?? '',
                    $b->consigneeAddress ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }


    // 🟩 Aggregate
    public function aggregate(Request $request)
    {
        $func = strtolower($request->aggregate_function ?? '');
        $col = $request->aggregate_column ?? '';
        $result = null;

        if ($func && $col && in_array($func, ['sum', 'avg', 'min', 'max', 'count'])) {
            $result = Booking::selectRaw(strtoupper($func) . "($col) as result")->value('result');
        }

        return redirect()->back()->with('aggregateResult', [
            'function' => $func,
            'column' => $col,
            'result' => $result,
        ]);
    }

    // 🟩 Compute
    public function compute(Request $request)
    {
        $expr = $request->compute_expression;
        $result = null;

        if ($expr) {
            try {
                $result = eval("return {$expr};"); // ⚠️ caution: only safe expressions
            } catch (\Throwable $e) {
                $result = 'Error';
            }
        }

        return redirect()->back()->with('computeResult', $result);
    }

    // 🟩 Row Filter
    public function rowFilter(Request $request)
    {
        $expression = $request->row_filter_expression;
        $query = Booking::query();

        if ($expression) {
            try {
                $query->whereRaw($expression);
            } catch (\Throwable $e) {
                return back()->with('error', 'Invalid filter expression');
            }
        }

        $filtered = $query->paginate(50);

        return view('searchdata', [
            'bookings' => $filtered,
            'visibleColumns' => session('visible_columns'),
            'aggregateResult' => session('aggregateResult'),
            'computeResult' => session('computeResult'),
        ])->with('filterApplied', true);
    }

    public function chart(Request $request)
    {
        $label = $request->input('label', 'customer_name');
        $value = $request->input('value', 'bookNo');
        $function = strtolower($request->input('function', 'count'));
        $chartType = $request->input('type', 'bar');
        $model = 'booking';

        if (!in_array($function, ['sum', 'avg', 'min', 'max', 'count'])) {
            return back()->with('error', 'Invalid aggregate function.');
        }

        try {
            $data = Booking::selectRaw("$label, $function($value) as aggregate")
                ->groupBy($label)
                ->orderBy($label)
                ->pluck('aggregate', $label);

            return view('chart', [
                'labels' => $data->keys(),
                'values' => $data->values(),
                'chartType' => $chartType,
                'labelTitle' => ucfirst(str_replace('_', ' ', $label)),
                'valueTitle' => ucfirst($function) . ' of ' . ucfirst(str_replace('_', ' ', $value)),
                'model' => $model,
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Chart generation failed: ' . $e->getMessage());
        }
    }

    // 🟩 Download
    public function download(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $query = Booking::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bookNo', 'like', "%$search%")
                    ->orWhereHas('customer', fn($q) => $q->where('customer_name', 'like', "%$search%"))
                    ->orWhere('product', 'like', "%$search%")
                    ->orWhere('origin', 'like', "%$search%")
                    ->orWhere('destination', 'like', "%$search%")
                    ->orWhere('shipperName', 'like', "%$search%")
                    ->orWhere('shipperContact', 'like', "%$search%")
                    ->orWhere('shipperAddress', 'like', "%$search%")
                    ->orWhere('consigneeName', 'like', "%$search%")
                    ->orWhere('consigneeContact', 'like', "%$search%");
            });
        }

        $data = $query->get()->map(function ($b) {
            return [
                'Book No' => $b->bookNo,
                'Book Date' => $b->book_date instanceof \Carbon\Carbon
                    ? $b->book_date->format('Y-m-d')
                    : (string) $b->book_date,
                'Customer' => $b->customer->customer_name ?? '',
                'Product' => $b->product,
                'Origin' => $b->origin,
                'Destination' => $b->destination,
                'Shipper Name' => $b->shipperName,
                'Shipper Contact' => $b->shipperContact,
                'Shipper Address' => $b->shipperAddress,
                'Consignee Name' => $b->consigneeName,
                'Consignee Contact' => $b->consigneeContact,
            ];
        });

        $filename = 'bookings_' . now()->format('Ymd_His') . '.' . $format;

        if ($format === 'csv') {
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];
            $callback = function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, array_keys($data[0] ?? []));
                foreach ($data as $row) fputcsv($handle, $row);
                fclose($handle);
            };
            return Response::stream($callback, 200, $headers);
        }

        if ($format === 'xlsx') {
            return Excel::download(new class($data)
            implements
                \Maatwebsite\Excel\Concerns\FromCollection,
                \Maatwebsite\Excel\Concerns\WithHeadings {
                private $data;
                public function __construct($data)
                {
                    $this->data = $data;
                }
                public function collection()
                {
                    return collect($this->data);
                }
                public function headings(): array
                {
                    return array_keys($this->data->first() ?? []);
                }
            }, $filename);
        }

        if ($format === 'html') {
            return response()->view('searchdata_download_html', ['data' => $data]);
        }

        return back()->with('error', 'Unsupported download format');
    }

    public function Pendingdownload(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $query = Booking::with(['customer', 'thirdparty', 'statuses']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bookNo', 'like', "%$search%")
                    ->orWhereHas('customer', fn($q) => $q->where('customer_name', 'like', "%$search%"))
                    ->orWhere('product', 'like', "%$search%")
                    ->orWhere('origin', 'like', "%$search%")
                    ->orWhere('destination', 'like', "%$search%")
                    ->orWhere('shipperName', 'like', "%$search%")
                    ->orWhere('shipperNumber', 'like', "%$search%")
                    ->orWhere('shipperAddress', 'like', "%$search%")
                    ->orWhere('consigneeName', 'like', "%$search%")
                    ->orWhere('consigneeNumber', 'like', "%$search%");
            });
        }

        $data = $query->get()->map(function ($b) {
            $thirdParty = $b->thirdparty ?? null;
            $latestStatus = $b->statuses->first();

            return [
                'Book No' => $b->bookNo,
                'Book Date' => $b->bookDate instanceof \Carbon\Carbon ? $b->bookDate->format('d-m-Y') : (string) $b->bookDate,
                'Status Date/Time' => $latestStatus ? $latestStatus->created_at->format('d-m-Y H:i') : '-',
                'Track Status' => $latestStatus->status ?? '-',
                'Customer' => $b->customer->customer_name ?? '-',
                'Product' => $b->product ?? $b->customer->product ?? '-',
                'Item Content' => $b->itemContent ?? $thirdParty->remarks ?? '-',
                'Payment Mode' => $b->paymentMode ?? '-',
                'Origin Country' => $b->originCountry ?? '-',
                'Origin' => $b->origin ?? '-',
                'Destination Country' => $b->destinationCountry ?? '-',
                'Destination' => $b->destination ?? '-',
                'Weight (KG)' => $b->weight ?? '-',
                'Pieces' => $b->pieces ?? '-',
                'Order No.' => $b->orderNo ?? '-',
                'Arrival Clearance' => $b->arrivalClearance ?? '-',
                '3PL Ref No.' => $thirdParty->ref_no ?? '-',
                '3PL Company' => $thirdParty->company_name ?? '-',
                'Courier Company' => $b->courierCompany ?? '-',
                'Ref No.' => $b->refNo ?? '-',
                'Shipper Name' => $b->shipperName ?? '-',
                'Shipper Contact No.' => $b->shipperNumber ?? '-',
                'Shipper Address' => $b->shipperAddress ?? '-',
                'Consignee Name' => $b->consigneeName ?? '-',
                'Consignee Contact No.' => $b->consigneeNumber ?? '-',
                'Consignee Address' => $b->consigneeAddress ?? '-',
                'COD Amount' => $b->codAmount ?? '-',
                'User ID' => $thirdParty->updated_by ?? '-',
            ];
        });

        $filename = 'bookings' . now()->format('Ymd_His') . '.' . $format;

        if ($format === 'csv') {
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];
            $callback = function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, array_keys($data[0] ?? []));
                foreach ($data as $row) fputcsv($handle, $row);
                fclose($handle);
            };
            return Response::stream($callback, 200, $headers);
        }

        if ($format === 'xlsx') {
            return Excel::download(new class($data)
            implements
                \Maatwebsite\Excel\Concerns\FromCollection,
                \Maatwebsite\Excel\Concerns\WithHeadings {
                private $data;
                public function __construct($data)
                {
                    $this->data = $data;
                }
                public function collection()
                {
                    return collect($this->data);
                }
                public function headings(): array
                {
                    return array_keys($this->data->first() ?? []);
                }
            }, $filename);
        }

        if ($format === 'html') {
            return response()->view('pending_download_html', ['data' => $data]);
        }

        return back()->with('error', 'Unsupported download format');
    }


    // 🟩 Set Visible Columns
    public function setColumns(Request $request)
    {
        $columns = $request->input('visible_columns', []);
        session(['visible_columns' => $columns]);
        return redirect()->back();
    }
    public function searchDataChart(Request $request)
    {
        // Default model name
        $model = $request->input('model', 'Booking');

        // Map human-friendly column names to DB columns
        $columnsMap = [
            'Book No' => 'bookNo',
            'Customer' => 'customer_name',
            'Product' => 'product',
            'Origin' => 'origin',
            'Destination' => 'destination',
            'Shipper Name' => 'shipperName',
            'Shipper Contact' => 'shipperNumber',
            'Shipper Address' => 'shipperAddress',
            'Consignee Name' => 'consigneeName',
            'Consignee Contact' => 'consigneeNumber',
        ];

        // Get label and value columns
        $labelColumn = $columnsMap[$request->input('label', 'Book No')] ?? 'bookNo';
        $valueColumn = $columnsMap[$request->input('value', 'Book No')] ?? 'bookNo';
        $function = strtolower($request->input('function', 'count'));
        $chartType = $request->input('type', 'bar');

        // Validate aggregate function
        if (!in_array($function, ['count', 'sum', 'avg', 'min', 'max'])) {
            return back()->with('error', 'Invalid aggregate function.');
        }

        // Build query
        try {
            if ($function === 'count') {
                $data = \App\Models\Booking::select($labelColumn)
                    ->selectRaw("COUNT($valueColumn) as aggregate")
                    ->groupBy($labelColumn)
                    ->orderBy($labelColumn)
                    ->pluck('aggregate', $labelColumn);
            } else {
                $data = \App\Models\Booking::select($labelColumn)
                    ->selectRaw(strtoupper($function) . "($valueColumn) as aggregate")
                    ->groupBy($labelColumn)
                    ->orderBy($labelColumn)
                    ->pluck('aggregate', $labelColumn);
            }

            return view('chart', [
                'labels' => $data->keys(),
                'values' => $data->values(),
                'chartType' => $chartType,
                'labelTitle' => ucfirst(str_replace('_', ' ', $labelColumn)),
                'valueTitle' => ucfirst($function) . ' of ' . ucfirst(str_replace('_', ' ', $valueColumn)),
                'model' => $model,
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Chart generation failed: ' . $e->getMessage());
        }
    }


    public function undertakingForm(Request $request)
    {
        $bookNo = $request->bookNo;

        $booking = null;

        if ($bookNo) {
            $booking = Booking::where('bookNo', $bookNo)->first();
        }

        return view('undertaking-print', compact('booking', 'bookNo'));
    }

    public function printUndertaking(Request $request)
    {
        $request->validate([
            'bookNo' => 'required|string'
        ]);

        $booking = Booking::where('bookNo', $request->bookNo)->first();

        if (!$booking) {
            // 🔴 Return to same form with error message
            return back()->withErrors(['bookNo' => 'No record found for this Book No.'])->withInput();
        }

        // Load the Blade view and convert to PDF
        $pdf = Pdf::loadView('undertaking-document', compact('booking'))
            ->setPaper('A4', 'portrait');

        // Option 1: Direct Download
        // return $pdf->download('undertaking-' . $booking->bookNo . '.pdf');

        // ✅ Option 2: Stream (open in browser)
        return $pdf->stream('undertaking-' . $booking->bookNo . '.pdf');
    }

    public function assigningCounterPartner()
    {
        $bookings = Booking::with(['customer', 'partner'])->latest()->paginate(50);
        $partners = Partner::all();

        return view('assigning-counter-partner', compact('bookings', 'partners'));
    }

    /**
     * Assign Partner to Booking
     */
    public function assignCounterPartner(Request $request)
    {
        $request->validate([
            'booking_id'  => 'required|exists:bookings,id',
            'partner_id'  => 'required|exists:partners,id',
            'assign_date' => 'required|date',
            'email_to'    => 'required',
            'email_cc'    => 'nullable',
        ]);
        $booking = Booking::findOrFail($request->booking_id);
        $partner = Partner::findOrFail($request->partner_id);
        // ✅ Save assignment
        $booking->partner_id = $partner->id;
        $booking->assigned_at = $request->assign_date;
        $booking->email_to = $request->email_to;
        $booking->email_cc = $request->email_cc;
        $booking->save();
        // ✅ Send Email
        $emailTo = explode(';', $request->email_to); // multiple recipients
        $emailCc = explode(';', $request->email_cc ?? '');
        Mail::to($emailTo)
            ->cc($emailCc)
            ->send(new AssignPartnerMail($booking));
        return redirect()->route('assigning.counter.partner')
            ->with('success', 'Counter partner assigned and email sent successfully!');
    }

    public function rowFilterAssigning(Request $request)
    {
        $query = Booking::query();

        if ($request->filled('counter_partner_id')) {
            $query->where('counter_partner_id', $request->counter_partner_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->get();

        return response()->json($bookings);
    }

    // Aggregate
    public function aggregateAssigning(Request $request)
    {
        $totalBookings = Booking::count();
        $totalWeight = Booking::sum('weight');
        $totalRevenue = Booking::sum('price');

        return response()->json([
            'totalBookings' => $totalBookings,
            'totalWeight' => $totalWeight,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    // Compute
    public function computeAssigning(Request $request)
    {
        $bookings = Booking::query();

        if ($request->filled('counter_partner_id')) {
            $bookings->where('counter_partner_id', $request->counter_partner_id);
        }

        // Example: compute some value, e.g., revenue per booking
        $computed = $bookings->get()->map(function ($booking) {
            $booking->revenueAfterCost = $booking->price - $booking->cost;
            return $booking;
        });

        return response()->json($computed);
    }

    // Download
    public function downloadAssigning()
    {
        $bookings = Booking::all();

        $csvHeader = ['Booking No', 'Customer', 'Counter Partner', 'Status', 'Price', 'Weight'];
        $filename = 'assigning_counter_partner.csv';

        $handle = fopen($filename, 'w');
        fputcsv($handle, $csvHeader);

        foreach ($bookings as $booking) {
            fputcsv($handle, [
                $booking->book_no,
                $booking->customer->name ?? '',
                $booking->counterPartner->name ?? '',
                $booking->status,
                $booking->price,
                $booking->weight
            ]);
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    // Chart
    public function chartAssigning(Request $request)
    {
        $data = Booking::select('counter_partner_id', DB::raw('count(*) as total'))
            ->groupBy('counter_partner_id')
            ->get()
            ->map(function ($item) {
                return [
                    'counter_partner' => $item->counterPartner->name ?? 'N/A',
                    'total' => $item->total
                ];
            });

        return response()->json($data);
    }

    // public function dashboard()
    // {
    //     $totalOrders = \App\Models\Booking::count(); // Total bookings
    //     $pendingOrders = \App\Models\Booking::whereDoesntHave('statuses', function($q){
    //         $q->where('status', 'DLV'); // Exclude delivered
    //     })->count();

    //     $deliveredOrders = \App\Models\Booking::whereHas('statuses', function($q){
    //         $q->where('status', 'DLV');
    //     })->count();

    //     $lastWeekOrders = \App\Models\Booking::whereBetween('created_at', [now()->subWeek(), now()])->count();
    //     $percentChange = $lastWeekOrders ? round((($totalOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 2) : 0;

    //     return view('dashboard.index', compact('totalOrders', 'pendingOrders', 'deliveredOrders', 'percentChange'));
    // }

    public function dashboard()
    {
        // Booking stats
        $totalOrders = \App\Models\Booking::count();
        $pendingOrders = \App\Models\Booking::whereDoesntHave('statuses', fn($q) => $q->where('status', 'Delieverd'))->count();
        $deliveredOrders = \App\Models\Booking::whereHas('statuses', fn($q) => $q->where('status', 'DLV'))->count();

        // Orders from last week to calculate percent change
        $lastWeekOrders = \App\Models\Booking::whereBetween('created_at', [now()->subWeek(), now()])->count();
        $percentChange = $lastWeekOrders ? round((($totalOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 2) : 0;

        // Logged-in user permissions
        $userPermissions = auth()->user()?->is_admin
            ? ['dashboard', 'booking', 'reports', 'financials', 'master-setup', 'book-tracking', 'label-print', 'operation']
            : auth()->user()?->permissions ?? [];

        // Pass data to the dashboard view
        return view('dashboard.index', compact('totalOrders', 'pendingOrders', 'deliveredOrders', 'percentChange', 'userPermissions'));
    }


    // Download selected bookings
    public function downloadPending(Request $request)
    {
        $bookingIds = $request->input('booking_ids', []);
        $bookings = Booking::whereIn('id', $bookingIds)->get();

        // Example: download CSV
        $csvData = [];
        foreach ($bookings as $b) {
            $csvData[] = [
                'Book No' => $b->bookNo,
                'Book Date' => $b->bookDate,
                'Customer' => $b->customer?->customer_name ?? '-',
                'Shipper' => $b->shipperName,
                // add other fields as needed
            ];
        }

        // Export CSV (simple example)
        $filename = 'pending_shipments_' . date('Ymd_His') . '.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array_keys($csvData[0] ?? []));
        foreach ($csvData as $row) fputcsv($handle, $row);
        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    // Bulk status update (optional)
    public function bulkStatus(Request $request)
    {
        $bookingIds = $request->input('booking_ids', []);
        Booking::whereIn('id', $bookingIds)->update(['status' => 'processed']); // example
        return redirect()->back()->with('success', 'Status updated successfully!');
    }
}
