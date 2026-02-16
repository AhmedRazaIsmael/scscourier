<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\ThirdPartyBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class OperationController extends Controller
{
    public function uploadForm()
    {
        session()->forget(['3pl_upload_data', '3pl_upload_columns', '3pl_mapping']);
        return view('3pl-upload', ['step' => 1]);
    }

    public function handleUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (count($rows) < 2) {
            return back()->with('error', 'CSV is empty or invalid.');
        }

        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        $data = [];
        foreach ($rows as $row) {
            $assoc = array_combine($header, array_pad($row, count($header), null));
            $data[] = $assoc;
        }

        session([
            '3pl_upload_columns' => $header,
            '3pl_upload_data' => $data,
        ]);

        return view('3pl-upload', [
            'step' => 2,
            'sourceColumns' => $header,
            'targetColumns' => [
                'BOOK_NO' => 'varchar2(20)',
                'TPL_REFNO' => 'varchar2(100)',
            ],
            'data' => $data,
        ]);
    }

    public function validateMapping(Request $request)
    {
        $mapping = $request->input('mapping');

        if (!$mapping || !is_array($mapping)) {
            return redirect()->back()->with('error', 'Please map all fields.');
        }

        session(['3pl_mapping' => $mapping]);

        return view('3pl-upload', [
            'step' => 3,
            'columns' => session('3pl_upload_columns'),
            'data' => session('3pl_upload_data'),
            'companies' => ['TCS', 'DHL', 'FEDEX', 'TRAX', 'M&P', 'Speedex', 'PostEx', 'Leopards', 'Swft'],
        ]);
    }

    public function uploadFinal(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string',
        ]);

        $data = session('3pl_upload_data');
        $mapping = session('3pl_mapping');

        $success = 0;
        $fail = 0;
        $messages = [];

        foreach ($data as $i => $row) {
            $bookNo = $row[$mapping['BOOK_NO']] ?? null;
            $refNo = $row[$mapping['TPL_REFNO']] ?? null;

            if (!$bookNo || !$refNo) {
                $fail++;
                $messages[] = "Row " . ($i + 2) . ": Missing BOOK_NO or TPL_REFNO.";
                continue;
            }

            ThirdPartyBooking::create([
                'book_no' => $bookNo,
                'ref_no' => $refNo,
                'company_name' => $request->company_name,
                'book_date' => now(),
                'remarks' => null,
            ]);

            $success++;
        }

        session()->forget(['3pl_upload_data', '3pl_upload_columns', '3pl_mapping']);

        return redirect()->route('3pl.upload.step1')
            ->with('success', "✅ {$success} uploaded, ❌ {$fail} failed.")
            ->with('messages', $messages);
    }


    public function create(Request $request)
    {

        $bookingList = Booking::whereNotIn('bookNo', function ($q) {
            $q->select('book_no')->from('third_party_bookings');
        })
            ->select('bookNo')
            ->orderBy('bookNo')
            ->get();

        // 🔹 Query with joins: bookings and customers
        $query = ThirdPartyBooking::query()
            ->leftJoin('bookings', 'third_party_bookings.book_no', '=', 'bookings.bookNo')
            ->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
            ->select(
                'third_party_bookings.*',
                'bookings.shipperName',
                'bookings.consigneeName',
                'customers.customer_name'
            );

        // 🔍 Search across multiple columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('third_party_bookings.book_no', 'like', "%$search%")
                    ->orWhere('third_party_bookings.company_name', 'like', "%$search%")
                    ->orWhere('third_party_bookings.ref_no', 'like', "%$search%")
                    ->orWhere('third_party_bookings.remarks', 'like', "%$search%")
                    ->orWhere('bookings.shipperName', 'like', "%$search%")
                    ->orWhere('bookings.consigneeName', 'like', "%$search%")
                    ->orWhere('customers.customer_name', 'like', "%$search%");
            });
        }

        // 🔸 Column Filter
        $allowedColumns = [
            'book_no',
            'company_name',
            'ref_no',
            'remarks',
            'customer_name',
            'shipperName',
            'consigneeName',
            'updated_by',
            'updated_at',
            'book_date'
        ];

        if ($request->filled('filter_column') && $request->filled('filter_operator') && $request->filled('filter_value')) {
            $col = $request->filter_column;
            $op = $request->filter_operator;
            $val = $request->filter_value;

            if ($op === 'like') $val = "%$val%";

            if (in_array($col, $allowedColumns)) {
                if ($col === 'customer_name') {
                    $query->where('customers.customer_name', $op, $val);
                } elseif (in_array($col, ['shipperName', 'consigneeName'])) {
                    $query->where('bookings.' . $col, $op, $val);
                } else {
                    $query->where('third_party_bookings.' . $col, $op, $val);
                }
            }
        }

        // 🔸 Sorting
        if ($request->has('sort_columns')) {
            foreach ($request->sort_columns as $sort) {
                $col = $sort['column'] ?? null;
                $dir = strtolower($sort['direction'] ?? 'asc');

                if ($col && in_array($dir, ['asc', 'desc']) && in_array($col, $allowedColumns)) {
                    if ($col === 'customer_name') {
                        $query->orderBy('customers.customer_name', $dir);
                    } elseif (in_array($col, ['shipperName', 'consigneeName'])) {
                        $query->orderBy('bookings.' . $col, $dir);
                    } else {
                        $query->orderBy('third_party_bookings.' . $col, $dir);
                    }
                }
            }
        } else {
            $query->latest('third_party_bookings.id');
        }

        // 📊 Aggregation (from session if redirected)
        $aggregateResult = session('aggregateResult');
        $computeResult = session('computeResult');

        // Pagination
        $bookings = $query->paginate(50)->appends($request->all());

        // Auto-generate Book No
        $latest = ThirdPartyBooking::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $nextBookNo = 'BOK-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Visible Columns
        $visibleColumns = session('visible_columns', [
            'book_no',
            'book_date',
            'company_name',
            'ref_no',
            'remarks',
            'customer_name',
            'shipperName',
            'consigneeName',
            'updated_by',
            'updated_at'
        ]);

        // Control Break (optional)
        $controlBreak = collect($request->input('control_break', []))
            ->filter(fn($row) => isset($row['column']) && $row['status'] === 'enabled')
            ->pluck('column')->toArray();




        return view('3pl-booking', compact(
            'bookings',
            'bookingList',
            'nextBookNo',
            'aggregateResult',
            'computeResult',
            'visibleColumns',
            'controlBreak'
        ));
    }


    // 🟩 Store new 3PL Booking
    public function store(Request $request)
    {
        // 1️⃣ Validate input
        $validated = $request->validate([
            'bookNo'     => 'required|string',
            '3plCompany' => 'required|string',
            'remarks'    => 'nullable|string',
        ]);

        // 2️⃣ Check if Booking exists
        $booking = Booking::where('bookNo', $validated['bookNo'])->first();
        if (!$booking) {
            return back()->with('error', 'Booking number does not exist.');
        }

        // 3️⃣ Check duplicate 3PL
        if (ThirdPartyBooking::where('book_no', $validated['bookNo'])->exists()) {
            return back()->with('error', 'This booking is already used for a 3PL booking.');
        }

        // 4️⃣ Prepare API payload
        $apiPayload = [
            'reference_number' => $booking->bookNo,
            'order_details' => $validated['remarks'] ?? 'No details',
            'customer_name' => $booking->consigneeName ?? 'Unknown',
            'customer_phone' => $booking->consigneeNumber ?? '00000000000', // Booking table se phone
            'special_instructions' => $validated['remarks'] ?? '',
            'pickup_address_code' => 'TMLO',
            'return_address_code' => 'TMLO',
            'destination_city' => $booking->destination ?? 'Unknown',
            'delivery_address' => $booking->consigneeAddress ?? 'Unknown',
            'ds_shipment_type' => 1,
            'store_id' => 1,
            'cod_amount' => $booking->codAmount ?? 0,
            'total_items' => $booking->totalItems ?? 1,
            'booking_weight' => $booking->weight ?? 1,
            'customer_email' => $booking->consigneeEmail ?? 'abc@gmail.com',
        ];

        // 5️⃣ Call Tranzo API
        try {
            $response = Http::withHeaders([
                'api-token' => '09f4924c715a474385938f7fef946e04',
                'Content-Type' => 'application/json'
            ])->post('https://api-integration.tranzo.pk/api/custom/v1/create-order/', $apiPayload);

            if ($response->successful()) {
                $apiData = $response->json();
                $trackingNumber = $apiData['tracking_number'] ?? null;

                if (!$trackingNumber) {
                    return back()->with('error', 'API call successful but no tracking number returned.');
                }

                // 6️⃣ Save in DB only after tracking number is received
                ThirdPartyBooking::create([
                    'book_no'      => $validated['bookNo'],
                    'book_date'    => now(),
                    'company_name' => $validated['3plCompany'],
                    'ref_no'       => $trackingNumber,
                    'remarks'      => $validated['remarks'],
                    'updated_by'   => Auth::id(),
                ]);

                // 7️⃣ Return success with tracking number
                return back()->with([
                    'success' => '3PL Booking created successfully!',
                    'tracking_number' => $trackingNumber
                ]);
            } else {
                // Agar API error ho, jaise delivery temporarily not operational
                $error = $response->json();
                return back()->with('error', 'Tranzo API Error: ' . json_encode($error));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'API Request Failed: ' . $e->getMessage());
        }
    }

    // 3PL Shipper Advice Api
    public function sendMerchantAdvice(Request $request)
    {
        try {
            $payload = [
                'orders' => [
                    [
                        'tracking_number' => $request->tracking_number,
                        'order_status'    => $request->order_status,
                        'remarks'         => $request->remarks
                    ]
                ]
            ];
            // dd($payload);
            $response = Http::withHeaders([
                'api-token' => '09f4924c715a474385938f7fef946e04',
                'Content-Type' => 'application/json'
            ])->post(
                'https://api-integration.tranzo.pk/api/custom/v1/create-merchant-advice/',
                $payload
            );

            // 🔥 IMPORTANT: return API response AS-IS
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'non_field_errors' => [$e->getMessage()]
            ], 500);
        }
    }

    // #PL Get Payment Status Api
    public function getPaymentStatus(Request $request)

    {
        try {
            // JS se tracking number query param me aayega
            $tracking = $request->query('tracking_number');

            // URL exactly Postman jaisa
            $url = 'https://api-integration.tranzo.pk/api/custom/v1/get-payment-status/?tracking_numbers=["' . $tracking . '"]';

            // API request using Laravel Http facade
            $response = Http::withHeaders([
                'api-token' => '09f4924c715a474385938f7fef946e04',
                'Content-Type' => 'application/json'
            ])->get($url);

            // Agar status 200 hai
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'error' => 'API returned ' . $response->status(),
                    'body'  => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        // Fetch 3PL bookings with related booking data
        $bookings = ThirdPartyBooking::with(['booking'])->paginate(10);

        return view('3pl-booking', compact('bookings'));
    }


    // 🟩 Aggregate
    public function aggregate(Request $request)
    {
        $func = strtolower($request->aggregate_function ?? '');
        $col = $request->aggregate_column ?? '';

        $result = null;

        if ($func && $col && in_array($func, ['sum', 'avg', 'min', 'max', 'count'])) {
            $result = ThirdPartyBooking::selectRaw(strtoupper($func) . "($col) as result")->value('result');
        }

        // ✅ Redirect back with session flash data
        return redirect()->back()->with('aggregateResult', [
            'function' => $func,
            'column' => $col,
            'result' => $result,
        ]);
    }

    // 🟩 Compute Expression
    public function compute(Request $request)
    {
        $expr = $request->compute_expression;
        $result = null;

        if ($expr) {
            try {
                // NOTE: Avoid dangerous eval usage in production
                $result = eval("return {$expr};");
            } catch (\Throwable $e) {
                $result = 'Error';
            }
        }

        // ✅ Redirect back with session data
        return redirect()->back()->with('computeResult', $result);
    }

    // 🟩 Row Filter
    public function rowFilter(Request $request)
    {
        $expression = $request->row_filter_expression;
        $query = ThirdPartyBooking::query();

        if ($expression) {
            try {
                $query->whereRaw($expression);
            } catch (\Throwable $e) {
                return back()->with('error', 'Invalid filter');
            }
        }

        $filtered = $query->paginate(50);

        // ✅ Return same view with filtered data
        return view('3pl-booking', [
            'bookings' => $filtered,
            'nextBookNo' => 'BOK-' . str_pad((ThirdPartyBooking::max('id') + 1), 4, '0', STR_PAD_LEFT),
            'visibleColumns' => session('visible_columns', ['book_no', 'book_date', 'company_name', 'ref_no', 'remarks']),
            'aggregateResult' => session('aggregateResult'),
            'computeResult' => session('computeResult'),
        ])->with('filterApplied', true);
    }

    // 🟩 Chart
    // public function chart(Request $request)
    // {
    //     $label = $request->input('label', 'company_name');
    //     $value = $request->input('value', 'book_no');
    //     $function = $request->input('function', 'count');
    //     $chartType = $request->input('type', 'bar');
    //     $model = $request->input('model', 'ThirdPartyBooking');

    //     $data = ThirdPartyBooking::select($label, DB::raw("$function($value) as aggregate"))
    //         ->groupBy($label)
    //         ->orderBy($label)
    //         ->pluck('aggregate', $label);

    //     // ✅ Redirect back with chart data (optional)
    //     return redirect()->back()->with('chartData', [
    //         'labels' => $data->keys(),
    //         'values' => $data->values(),
    //         'chartType' => $chartType,
    //         'labelTitle' => ucfirst(str_replace('_', ' ', $label)),
    //         'valueTitle' => ucfirst($function) . ' of ' . ucfirst(str_replace('_', ' ', $value)),
    //     ]);
    // }
    public function chart(Request $request)
    {
        $label = $request->input('label', 'company_name');
        $value = $request->input('value', 'book_no');
        $function = $request->input('function', 'count');
        $chartType = $request->input('type', 'bar');

        $data = ThirdPartyBooking::select($label, DB::raw("$function($value) as aggregate"))
            ->groupBy($label)
            ->orderBy($label)
            ->pluck('aggregate', $label);
        return view('chart', [
            'labels' => $data->keys(),
            'values' => $data->values(),
            'chartType' => $chartType,
            'labelTitle' => ucfirst(str_replace('_', ' ', $label)),
            'valueTitle' => ucfirst($function) . ' of ' . ucfirst(str_replace('_', ' ', $value)),
            'model' => 'ThirdPartyBooking'
        ]);
    }

    // 🟩 Download / Export

    public function download()
    {
        // 🔹 Eager load relations
        $bookings = \App\Models\ThirdPartyBooking::with(['updater', 'booking.customer'])
            ->leftJoin('bookings', 'third_party_bookings.book_no', '=', 'bookings.bookNo')
            ->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
            ->select(
                'third_party_bookings.*',
                'bookings.shipperName',
                'bookings.consigneeName',
                'customers.customer_name'
            )
            ->orderBy('third_party_bookings.book_no')
            ->get();

        if ($bookings->isEmpty()) {
            return back()->with('error', 'No booking data available to download.');
        }

        $fileName = '3pl_bookings_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $columns = [
            'Book No',
            'Book Date',
            '3PL Company',
            '3PL Ref No',
            'Remarks',
            'Customer Name',
            'Shipper Name',
            'Consignee Name',
            'Updated By',
            'Updated Date/Time'
        ];

        $callback = function () use ($bookings, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($bookings as $b) {
                fputcsv($file, [
                    $b->book_no,
                    optional($b->book_date)->format('d-m-Y'),
                    $b->company_name,
                    $b->ref_no,
                    $b->remarks,
                    $b->customer_name ?? '-',                 // from customers table
                    $b->shipperName ?? '-',                  // from bookings
                    $b->consigneeName ?? '-',                // from bookings
                    $b->updater->name ?? '-',                // from updater() relationship
                    optional($b->updated_at)->format('d-m-Y H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    // 🟩 Set Visible Columns
    public function setColumns(Request $request)
    {
        $columns = $request->input('visible_columns', []);
        session(['visible_columns' => $columns]);
        return redirect()->back();
    }

    public function flashback(Request $request)
    {
        $date = $request->input('flashback_date', now()->format('Y-m-d'));

        $bookings = ThirdPartyBooking::whereDate('book_date', $date)
            ->paginate(50);

        $nextBookNo = 'BOK-' . str_pad((ThirdPartyBooking::max('id') + 1), 4, '0', STR_PAD_LEFT);

        return view('3pl-booking', [
            'bookings' => $bookings,
            'nextBookNo' => $nextBookNo,
            'visibleColumns' => session('visible_columns', ['book_no', 'book_date', 'company_name', 'ref_no', 'remarks']),
            'aggregateResult' => session('aggregateResult'),
            'computeResult' => session('computeResult'),
            'flashbackDate' => $date,
        ])->with('flashbackApplied', true);
    }

    public function highlight(Request $request)
    {
        $keyword = $request->input('highlight_keyword');

        $bookings = ThirdPartyBooking::all();

        // Pass an array of IDs to highlight
        $highlightIds = [];
        if ($keyword) {
            $highlightIds = $bookings->filter(function ($b) use ($keyword) {
                return str_contains(strtolower($b->book_no), strtolower($keyword)) ||
                    str_contains(strtolower($b->company_name ?? ''), strtolower($keyword)) ||
                    str_contains(strtolower($b->ref_no ?? ''), strtolower($keyword));
            })->pluck('id')->toArray();
        }

        $bookings = ThirdPartyBooking::paginate(50);

        return view('3pl-booking', [
            'bookings' => $bookings,
            'nextBookNo' => 'BOK-' . str_pad((ThirdPartyBooking::max('id') + 1), 4, '0', STR_PAD_LEFT),
            'visibleColumns' => session('visible_columns', ['book_no', 'book_date', 'company_name', 'ref_no', 'remarks']),
            'aggregateResult' => session('aggregateResult'),
            'computeResult' => session('computeResult'),
            'highlightIds' => $highlightIds
        ]);
    }

    public function downloadSampleCsv()
    {
        $columns = [
            'BOOK_NO',
            'TPL_REFNO',
        ];

        $sampleData = [
            [
                'BOOK_NO' => 'BK123456',
                'TPL_REFNO' => 'TPL7891011',
            ]
        ];

        $filename = "3pl_sample_bookings.csv";

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
}
