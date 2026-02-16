<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Milon\Barcode\Facades\DNS2DFacade; // ✅ Correct import

class LabelController extends Controller
{
    // Show Single Label Form
    public function singleLabelForm()
    {
        return view('single-label');
    }

    // Print Single Label PDF
    // public function printSingleLabel(Request $request)
    // {
    //     $request->validate(['bookNo' => 'required']);
    //     $booking = Booking::with('customer')->where('bookNo', $request->bookNo)->firstOrFail();
    //     $fileName = 'qr_' . $booking->id . '_' . Str::random(5) . '.png';
    //     $filePath = public_path('temp/' . $fileName);
    //     File::ensureDirectoryExists(public_path('temp'));
    //     $qrData = DNS2DFacade::getBarcodePNG($booking->bookNo, 'QRCODE');
    //     File::put($filePath, base64_decode($qrData));
    //     $booking->qr_path = $filePath;


    //     $pdf = Pdf::loadView('pdf.single-label', compact('booking'));
    //     return $pdf->stream("SingleLabel-{$booking->bookNo}.pdf");
    // }

    public function printSingleLabel(Request $request)
    {
        $request->validate(['bookNo' => 'required']);

        // Check if booking exists
        $booking = Booking::with('customer')->where('bookNo', $request->bookNo)->first();

        if (!$booking) {
            // Return back to the same form with an error
            return back()->withErrors(['bookNo' => 'No record found for this Book No.'])->withInput();
        }

        $fileName = 'qr_' . $booking->id . '_' . Str::random(5) . '.png';
        $filePath = public_path('temp/' . $fileName);
        File::ensureDirectoryExists(public_path('temp'));
        $qrData = DNS2DFacade::getBarcodePNG($booking->bookNo, 'QRCODE');
        File::put($filePath, base64_decode($qrData));
        $booking->qr_path = $filePath;

        $pdf = Pdf::loadView('pdf.single-label', compact('booking'));
        return $pdf->stream("SingleLabel-{$booking->bookNo}.pdf");
    }



    public function bulkLabelForm()
    {
        $bookings = Booking::with('customer')->latest()->paginate(50);
        return view('bulk-label', compact('bookings'));
    }

    // Print Bulk Labels as PDF

    public function printBulkLabel(Request $request)
    {
        $request->validate([
            'booking_ids' => 'required|array',
        ]);

        $bookings = Booking::with('customer')
            ->whereIn('id', $request->booking_ids)
            ->get();
        // Save QR codes as images
        foreach ($bookings as $booking) {
            $fileName = 'qr_' . $booking->id . '_' . Str::random(5) . '.png';
            $filePath = public_path('temp/' . $fileName);

            // Make temp directory if needed
            File::ensureDirectoryExists(public_path('temp'));

            // Generate raw PNG
            $qrData = DNS2DFacade::getBarcodePNG($booking->bookNo, 'QRCODE');

            // Save image
            File::put($filePath, base64_decode($qrData));

            // Pass image path to view
            $booking->qr_path = $filePath;
        }


        if ($bookings->isEmpty()) {
            return back()->with('error', 'No bookings selected.');
        }

        $pdf = Pdf::loadView('pdf.bulk', compact('bookings'));
        return $pdf->stream("BulkLabel.pdf");
    }


    // Show Sticker Label Form
    public function stickerLabelForm()
    {
        return view('sticker-single-label'); // form wala page
    }

    // Print Sticker Label PDF
    public function printStickerLabel(Request $request)
    {
        $request->validate(['bookNo' => 'required']);

        $booking = Booking::with('customer')->where('bookNo', $request->bookNo)->first();

        if (!$booking) {
            return back()->withErrors(['bookNo' => 'No record found for this Book No.'])->withInput();
        }

        $fileName = 'qr_' . $booking->id . '_' . Str::random(5) . '.png';
        $filePath = public_path('temp/' . $fileName);
        File::ensureDirectoryExists(public_path('temp'));
        $qrData = DNS2DFacade::getBarcodePNG($booking->bookNo, 'QRCODE');
        File::put($filePath, base64_decode($qrData));
        $booking->qr_path = $filePath;

        $pdf = Pdf::loadView('pdf.sticker', compact('booking'))->setPaper('A5', 'portrait');

        return $pdf->stream("StickerLabel-{$booking->bookNo}.pdf");
    }

    public function printBulkPODLabel(Request $request)
    {
        $request->validate([
            'booking_ids' => 'required|array',
        ]);

        $bookings = Booking::with('customer')->whereIn('id', $request->booking_ids)->get();

        if ($bookings->isEmpty()) {
            return back()->with('error', 'No bookings selected.');
        }

        // Generate QR codes for each booking
        foreach ($bookings as $booking) {
            $fileName = 'qr_' . $booking->id . '_' . Str::random(5) . '.png';
            $filePath = public_path('temp/' . $fileName);

            // Make temp directory if it doesn't exist
            File::ensureDirectoryExists(public_path('temp'));

            // Generate raw PNG QR code (using DNS2D)
            $qrData = DNS2DFacade::getBarcodePNG($booking->bookNo, 'QRCODE');

            // Save PNG to temp folder
            File::put($filePath, base64_decode($qrData));

            // Pass image path to the booking object
            $booking->qr_path = $filePath;
        }

        $pdf = Pdf::loadView('pdf.bulk-pod-label', compact('bookings'))->setPaper('a4');
        return $pdf->stream("BulkPODLabel.pdf");
    }

    public function bulkPODForm()
    {
        $bookings = Booking::with('customer')->latest()->paginate(50);
        return view('pdo-bulk-label', compact('bookings'));
    }

    public function podBookingsJson()
    {
        $bookings = Booking::with('customer')->latest()->get();

        if ($bookings->isEmpty()) {
            return response()->json([]);
        }

        return response()->json($bookings->map(function ($b) {
            return [
                'id' => $b->id,
                'bookNo' => $b->bookNo ?? '-',
                'bookDate' => $b->bookDate
                    ? \Carbon\Carbon::parse($b->bookDate)->format('d-M-Y')
                    : \Carbon\Carbon::parse($b->created_at)->format('d-M-Y'),

                'company' => $b->consigneeCompany ?? '-',
                'customer' => $b->customer->customer_name ?? '-', // if customer relation exists

                'product' => $b->itemContent ?? '-',
                'service' => $b->service ?? '-',
                'itemContent' => $b->itemContent ?? '-',
                'paymentMode' => $b->paymentMode ?? '-',
                'origin' => $b->origin ?? '-',
                'destination' => $b->destination ?? '-',
                'weight' => $b->weight ?? '0',
                'pieces' => $b->pieces ?? '0',
                'length' => $b->length ?? '-',
                'width' => $b->width ?? '-',
                'height' => $b->height ?? '-',
                'dimensionalWeight' => $b->dimensionalWeight ?? '-',
                'orderNo' => $b->orderNo ?? '-',
                'shipperName' => $b->shipperName ?? '-',
                'shipperNumber' => $b->shipperNumber ?? '-',
                'shipperAddress' => $b->shipperAddress ?? '-',
                'consigneeName' => $b->consigneeName ?? '-',
                'consigneeNumber' => $b->consigneeNumber ?? '-',
                'consigneeAddress' => $b->consigneeAddress ?? '-',

                'booking' => $b, // for checkbox value
            ];
        }));
    }

    public function generateSinglePOD(Request $request)
    {
        $booking = Booking::with('customer')->findOrFail($request->booking_id);

        $pdf = PDF::loadView('pdf.pod-single', compact('booking'))->setPaper('A4', 'portrait');

        return $pdf->stream("POD-{$booking->bookNo}.pdf");
    }

    public function printSinglePOD(Request $request)
    {
        $request->validate(['bookNo' => 'required']);

        $booking = Booking::with('customer')->where('bookNo', $request->bookNo)->firstOrFail();

        // Generate QR code if not exists
        $fileName = 'qr_' . $booking->id . '_' . Str::random(5) . '.png';
        $filePath = public_path('temp/' . $fileName);
        File::ensureDirectoryExists(public_path('temp'));

        $qrData = DNS2DFacade::getBarcodePNG($booking->bookNo, 'QRCODE');
        File::put($filePath, base64_decode($qrData));
        $booking->qr_path = $filePath;

        $pdf = Pdf::loadView('pdf.pod-single', compact('booking'));

        if ($request->has('download')) {
            return $pdf->download("SinglePOD-{$booking->bookNo}.pdf");
        }

        return $pdf->stream("SinglePOD-{$booking->bookNo}.pdf");
    }



    public function searchSinglePOD(Request $request)
    {
        $booking = null;

        if ($request->filled('bookNo')) {
            $booking = Booking::where('bookNo', $request->bookNo)->first();
        }

        return view('pdo-single-label', compact('booking'));
    }

    public function salesFunnel(Request $request)
    {
        $salesPersonId = $request->salesPerson;

        // Build query FIRST (don't call get() yet)
        $query = Booking::with(['customer', 'salesPersonUser', 'territoryUser']);

        // Apply filter only if needed
        if ($salesPersonId) {
            $query->where('salesPerson', $salesPersonId);
        }

        // Now execute the query
        $bookings = $query->get();

        $today = now();

        // Categorize each booking
        $funnelCounts = [
            'Active' => 0,
            'Need Attention' => 0,
            'Need Serious Attention' => 0,
            'Dormant' => 0,
            'Lost' => 0,
        ];

        $tableData = [];

        foreach ($bookings as $booking) {
            $daysSinceLastSale = $today->diffInDays(\Carbon\Carbon::parse($booking->bookDate));
            $level = '';
            if ($daysSinceLastSale <= 30) {
                $funnelCounts['Active']++;
                $level = 'Active';
            } elseif ($daysSinceLastSale <= 60) {
                $funnelCounts['Need Attention']++;
                $level = 'Need Attention';
            } elseif ($daysSinceLastSale <= 90) {
                $funnelCounts['Need Serious Attention']++;
                $level = 'Need Serious Attention';
            } elseif ($daysSinceLastSale <= 120) {
                $funnelCounts['Dormant']++;
                $level = 'Dormant';
            } else {
                $funnelCounts['Lost']++;
                $level = 'Lost';
            }

            $tableData[] = [
                'territory' => optional($booking->territoryUser)->name ?? '-',
                'sales_person' => optional($booking->salesPersonUser)->name ?? '-', // fallback
                'customer' => optional($booking->customer)->customer_name ?? '-',   // fixed
                'bookingType' => ucfirst($booking->bookingType),
                'days' => $daysSinceLastSale,
                'level' => $level,
                'last_date' => \Carbon\Carbon::parse($booking->bookDate)->format('d-M-Y'),
            ];
        }

        $salesPeople = User::all();

        return view('sales-funnel', [
            'funnel' => $funnelCounts,
            'salesPeople' => $salesPeople,
            'salesPersonId' => $salesPersonId,
            'tableData' => $tableData,
        ]);
    }

    public function printSingleLabelGet(Request $request)
    {
        $request->validate(['bookNo' => 'required']);

        $booking = Booking::with('customer')->where('bookNo', $request->bookNo)->firstOrFail();
        if (!$booking) {
            return redirect()->back()->with('error', 'No booking found for the provided Book No.');
        }


        $fileName = 'qr_' . $booking->id . '_' . Str::random(5) . '.png';
        $filePath = public_path('temp/' . $fileName);
        File::ensureDirectoryExists(public_path('temp'));

        $qrData = DNS2DFacade::getBarcodePNG($booking->bookNo, 'QRCODE');
        File::put($filePath, base64_decode($qrData));
        $booking->qr_path = $filePath;

        $pdf = Pdf::loadView('pdf.single-label', compact('booking'));

        // If user clicked "Download" button
        if ($request->has('download')) {
            return $pdf->download("SingleLabel-{$booking->bookNo}.pdf");
        }

        // Otherwise, open in browser (Print)
        return $pdf->stream("SingleLabel-{$booking->bookNo}.pdf");
    }

    // Column Filter
    public function bulkFilter(Request $request)
    {
        $query = Booking::query();

        if ($request->filled('filter_column') && $request->filled('filter_operator') && $request->filled('filter_value')) {
            $column = $request->filter_column;
            $operator = $request->filter_operator;
            $value = $request->filter_value;

            if ($operator === 'like') {
                $query->where($column, 'like', "%{$value}%");
            } else {
                $query->where($column, $operator, $value);
            }
        }

        $bookings = $query->with('customer')->latest()->paginate(50);
        return view('bulk-label', compact('bookings'));
    }

    // Row Filter
    public function bulkRowFilter(Request $request)
    {
        $bookings = Booking::with('customer')->get();

        if ($request->filled('row_filter_expression')) {
            $expression = $request->row_filter_expression;

            $bookings = $bookings->filter(function ($item) use ($expression) {
                $expr = $expression;
                foreach ($item->getAttributes() as $key => $val) {
                    $expr = str_replace($key, "'" . $val . "'", $expr);
                }
                return eval("return {$expr};"); // ⚠️ eval, be careful
            });
        }

        return view('bulk-label', ['bookings' => $bookings]);
    }

    // Sort
    public function bulkSort(Request $request)
    {
        $query = Booking::query();

        if ($request->filled('sort_columns')) {
            foreach ($request->sort_columns as $sort) {
                if (!empty($sort['column']) && in_array($sort['direction'], ['asc', 'desc'])) {
                    $query->orderBy($sort['column'], $sort['direction']);
                }
            }
        }

        $bookings = $query->with('customer')->latest()->paginate(50);
        return view('bulk-label', compact('bookings'));
    }

    // Aggregate
    public function bulkAggregate(Request $request)
    {
        $function = $request->aggregate_function;
        $column = $request->aggregate_column;

        $result = null;
        switch ($function) {
            case 'count':
                $result = Booking::count($column);
                break;
            case 'sum':
                $result = Booking::sum($column);
                break;
            case 'avg':
                $result = Booking::avg($column);
                break;
            case 'min':
                $result = Booking::min($column);
                break;
            case 'max':
                $result = Booking::max($column);
                break;
        }

        return redirect()->back()->with('aggregateResult', [
            'function' => $function,
            'column' => $column,
            'result' => $result
        ]);
    }

    // Compute (expression-based)
    public function bulkCompute(Request $request)
    {
        $expression = $request->compute_expression;
        $bookings = Booking::all();
        $results = [];

        foreach ($bookings as $booking) {
            $expr = $expression;
            foreach ($booking->getAttributes() as $key => $val) {
                $expr = str_replace($key, "'" . $val . "'", $expr);
            }
            $results[] = eval("return {$expr};"); // ⚠️ eval, be careful
        }

        return redirect()->back()->with('computeResult', implode(', ', $results));
    }

    public function bulkChart(Request $request)
    {
        $label = $request->input('label', 'customer'); // default label
        $value = $request->input('value', 'bookNo');   // default value
        $function = $request->input('function', 'count');
        $chartType = $request->input('type', 'bar');

        // Map your Booking fields to human-readable titles
        $columnNames = [
            'weight' => 'Weight (KG)',
            'pieces' => 'Pieces',
            'shipperName' => 'Shipper Name',
            'shipperNumber' => 'Shipper Contact No.',
            'shipperAddress' => 'Shipper Address',
            'bookNo' => 'Booking Number',
            'bookDate' => 'Book Date',
            'customer' => 'Customer',
            'bookingType' => 'Booking Type',
            'service' => 'Service',
            'itemContent' => 'Item Content',
            'origin' => 'Origin',
            'destination' => 'Destination',
            'consigneeName' => 'Consignee Name',
            'consigneeNumber' => 'Consignee Contact No.'
        ];

        // Fetch aggregated data
        $data = Booking::select($label, DB::raw("$function($value) as aggregate"))
            ->groupBy($label)
            ->orderBy($label)
            ->get();

        return view('chart', [
            'labels' => $data->pluck($label),
            'values' => $data->pluck('aggregate'),
            'chartType' => $chartType,
            'labelTitle' => $columnNames[$label] ?? ucfirst(str_replace('_', ' ', $label)),
            'valueTitle' => ucfirst($function) . ' of ' . ($columnNames[$value] ?? ucfirst(str_replace('_', ' ', $value))),
            'model' => 'Booking'
        ]);
    }

    // Download
    public function bulkDownload(Request $request)
    {
        $format = $request->format ?? 'csv';
        $ids = $request->booking_ids ?? Booking::pluck('id')->toArray();
        $bookings = Booking::whereIn('id', $ids)->get();

        if ($format === 'csv') {
            $csv = '';
            $columns = array_keys($bookings->first()->getAttributes());
            $csv .= implode(',', $columns) . "\n";

            foreach ($bookings as $row) {
                $csv .= implode(',', $row->toArray()) . "\n";
            }

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="bookings.csv"');
        }

        if ($format === 'xlsx') {
            // Implement Excel export if needed
        }

        if ($format === 'html') {
            return view('bulk-label-download', compact('bookings'));
        }
    }
}
