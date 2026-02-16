<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\ExportInvoice;
use App\Models\ImportInvoice;
use App\Models\InvoiceRecovery;
use App\Models\ExportInvoiceItem;
use App\Models\ImportInvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Display Import Invoicing page.
     */
    public function import(Request $request)
    {
       $query = ImportInvoice::with(['customer', 'items'])->latest();
        // Optional search filter
        if ($request->filled('search')) {
            $query->where('invoice_no', 'like', '%' . $request->search . '%');
        }
    
        $bookings = $query->paginate(50);
    
        return view('invoicing.import', compact('bookings'));
    }


    /**
     * Display Export Invoicing page.
     */
    

    /**
     * Display Invoice Detail Report page with optional filters.
     */
    public function report(Request $request)
    {
        $query = Booking::with('customer');

        // 📅 Date range filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('bookDate', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay(),
            ]);
        }

        // 👤 Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $bookings = $query->latest()->paginate(50);
        $customers = Customer::all();

        return view('invoicing.report', compact('bookings', 'customers'));
    }

    public function uninvoicedImport(Request $request)
    {
        // 1. Get bookings that are not invoiced (assuming they are not in import_invoices table)
        $invoicedBookNos = ImportInvoiceItem::pluck('book_no')->toArray();
    
        $bookings = Booking::with('customer')
            ->where('bookingType', 'import')
            ->whereNotIn('bookNo', $invoicedBookNos)
            ->latest()
            ->paginate(50);
    
        // 2. Monthly Pending Count
        $monthWise = Booking::selectRaw("DATE_FORMAT(bookDate, '%b-%Y') as month, COUNT(*) as total")
            ->where('bookingType', 'import')
            ->whereNotIn('bookNo', $invoicedBookNos)
            ->groupBy('month')
            ->orderByRaw("STR_TO_DATE(month, '%b-%Y')")
            ->pluck('total', 'month');
    
        // 3. Customer Wise Count
        $customerWise = Booking::selectRaw("customers.customer_name as customer, COUNT(*) as total")
            ->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->where('bookingType', 'import')
            ->whereNotIn('bookNo', $invoicedBookNos)
            ->groupBy('customers.customer_name')
            ->pluck('total', 'customer');
    
        return view('invoicing.un-invoice-import', compact('bookings', 'monthWise', 'customerWise'));
    }

     // ------------------ EXPORT ------------------

   public function export()
    {
        $invoices = ExportInvoice::with('customer')->latest()->paginate(50);
        return view('invoicing.export', compact('invoices'));
    }

    public function exportCreate()
    {
        $customers = Customer::all();
        $invoice_no = $this->generateInvoiceNumber(); 
        return view('invoicing.export-create', compact('customers', 'invoice_no')); // corrected file name
    }

    public function exportStore(Request $request)
    {
        $request->validate([
            'invoice_no'   => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'pay_due_date' => 'required|date',
            'pay_mode'     => 'nullable|string|max:50',
            'customer_id'  => 'required|exists:customers,id',
            'remarks'      => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.book_no' => 'nullable|string|max:255',
            'items.*.consignee' => 'nullable|string|max:255',
            'items.*.account_head' => 'nullable|string|max:255',
            'items.*.currency' => 'nullable|string|max:10',
            'items.*.currency_rate' => 'nullable|numeric',
            'items.*.gross_weight' => 'nullable|numeric',
            'items.*.rate' => 'nullable|numeric',
            'items.*.amount' => 'nullable|numeric',
            'items.*.freight_rate' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            $invoice = ExportInvoice::create([
                'invoice_no'   => $this->generateInvoiceNumber(),
                'invoice_date' => $request->invoice_date,
                'pay_due_date' => $request->pay_due_date,
                'pay_mode'     => $request->pay_mode,
                'customer_id'  => $request->customer_id,
                'remarks'      => $request->remarks,
            ]);

            foreach ($request->items as $item) {
                // Skip if all fields are empty
                if (!array_filter($item)) continue;

                $invoice->items()->create([
                    'book_no'       => $item['book_no'] ?? null,
                    'consignee'     => $item['consignee'] ?? null,
                    'account_head'  => $item['account_head'] ?? null,
                    'currency'      => $item['currency'] ?? null,
                    'currency_rate' => $item['currency_rate'] ?? null,
                    'gross_weight'  => $item['gross_weight'] ?? null,
                    'rate'          => $item['rate'] ?? null,
                    'amount'        => $item['amount'] ?? null,
                    'freight_rate'  => $item['freight_rate'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('invoice.export')->with('success', 'Export invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Something went wrong.')->withInput();
        }
    }


    public function exportEdit($id)
    {
        $invoice = ExportInvoice::with(['customer', 'items'])->findOrFail($id);
        return view('invoicing.export-edit', compact('invoice'));
    }

    public function exportUpdateItems(Request $request, $id)
    {
        $invoice = ExportInvoice::findOrFail($id);

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'nullable|exists:export_invoice_items,id',
            'items.*.book_no' => 'nullable|string|max:255',
            'items.*.consignee' => 'nullable|string|max:255',
            'items.*.account_head' => 'nullable|string|max:255',
            'items.*.currency' => 'nullable|string|max:50',
            'items.*.currency_rate' => 'nullable|numeric',
            'items.*.gross_weight' => 'nullable|numeric',
            'items.*.rate' => 'nullable|numeric',
            'items.*.amount' => 'nullable|numeric',
            'items.*.freight_rate' => 'nullable|numeric',
        ]);

        foreach ($request->items as $item) {
            if (isset($item['id'])) {
                $invoice->items()->where('id', $item['id'])->update($item);
            } else {
                $invoice->items()->create($item);
            }
        }

        return redirect()->route('invoice.export.edit', $invoice->id)->with('success', 'Invoice items updated successfully.');
    }

    public function exportPrint($id)
    {
        $invoice = ExportInvoice::with('customer', 'items')->findOrFail($id);

        return view('invoicing.export-print', compact('invoice'));
    }


    //  public function invoiceRecovery(Request $request)
    // {
    //     $minBalance = $request->get('min_balance', 0); 

    //     $exportInvoices = ExportInvoice::with(['customer', 'items'])->get();
    //     $importInvoices = ImportInvoice::with(['customer', 'items'])->get();

    //     $invoices = collect();

    //     // Export invoices
    //     foreach ($exportInvoices as $invoice) {
    //         $invoice_amount = $invoice->items->sum('amount');
    //         $recovered_amount = DB::table('invoice_recoveries')
    //             ->where('invoice_id', $invoice->id)
    //             ->where('invoice_type', 'export')
    //             ->sum('recovery_amount');

    //         $balance = $invoice_amount - $recovered_amount;

    //         if ($balance >= $minBalance) {
    //             $invoices->push((object)[
    //                 'id' => $invoice->id,
    //                 'invoice_no' => $invoice->invoice_no,
    //                 'invoice_date' => $invoice->invoice_date,
    //                 'pay_due_date' => $invoice->pay_due_date,
    //                 'customer' => $invoice->customer->customer_name ?? '-',
    //                 'product' => 'Export',
    //                 'invoice_amount' => $invoice_amount,
    //                 'recovered_amount' => $recovered_amount,
    //                 'balance' => $balance,
    //                 'type' => 'export', // <-- key addition
    //             ]);
    //         }
    //     }

    //     // Import invoices
    //     foreach ($importInvoices as $invoice) {
    //         $invoice_amount = $invoice->items->sum('amount');
    //         $recovered_amount = DB::table('invoice_recoveries')
    //             ->where('invoice_id', $invoice->id)
    //             ->where('invoice_type', 'import')
    //             ->sum('recovery_amount');

    //         $balance = $invoice_amount - $recovered_amount;

    //         if ($balance >= $minBalance) {
    //             $invoices->push((object)[
    //                 'id' => $invoice->id,
    //                 'invoice_no' => $invoice->invoice_no,
    //                 'invoice_date' => $invoice->invoice_date,
    //                 'pay_due_date' => $invoice->pay_due_date,
    //                 'customer' => $invoice->customer->customer_name ?? '-',
    //                 'product' => 'Import',
    //                 'invoice_amount' => $invoice_amount,
    //                 'recovered_amount' => $recovered_amount,
    //                 'balance' => $balance,
    //                 'type' => 'import', // <-- key addition
    //             ]);
    //         }
    //     }

    //     // Totals
    //     $totals = [
    //         'invoice_amount' => $invoices->sum('invoice_amount'),
    //         'recovered_amount' => $invoices->sum('recovered_amount'),
    //         'balance' => $invoices->sum('balance'),
    //     ];

    //     // Customer-wise outstanding
    //     $customerWise = $invoices->groupBy('customer')->map(fn($group) => $group->sum('balance'));

    //     return view('invoicing.recovery-index', compact('invoices', 'totals', 'customerWise'));
    // }

    // // Show Invoice Recovery Detail Page
    // public function showRecoveryInvoice($id, $type)
    // {
    //     if ($type === 'import') {
    //         $invoice = ImportInvoice::with('customer', 'items')->findOrFail($id);
    //     } elseif ($type === 'export') {
    //         $invoice = ExportInvoice::with('customer', 'items')->findOrFail($id);
    //     } else {
    //         abort(404, 'Invalid invoice type');
    //     }
    //     $invoiceType = $type;
    //      // Load recoveries only for this invoice and type
    //     $previousRecoveries = DB::table('invoice_recoveries')
    //         ->where('invoice_id', $invoice->id)
    //         ->where('invoice_type', $invoiceType)
    //         ->orderBy('created_at', 'desc')
    //         ->get()
    //         ->map(function($r) use ($invoice) {
    //             $r->invoice_no = $invoice->invoice_no;
    //             $r->customer_name = $invoice->customer->customer_name ?? '-';
    //             return $r;
    //         });

    //     return view('invoicing.invoice-recovery-detail', compact('invoice', 'previousRecoveries'));
    // }


    // // Save Recovery Entry via AJAX
    // public function saveRecovery(Request $request)
    // {
    //     $request->validate([
    //         'invoice_id'       => 'required|integer',
    //         'invoice_type'     => 'required|string',
    //         'recovery_person'  => 'required|string',
    //         'receiving_path'   => 'required|string',
    //         'recovery_amount'  => 'required|numeric|min:1',
    //         'remarks'          => 'nullable|string',
    //     ]);

    //     // Find invoice to get customer name for display
    //     $invoice = null;
    //     $customerName = '-';
    //     if ($request->invoice_type === 'export') {
    //         $invoice = \App\Models\ExportInvoice::with('customer')->findOrFail($request->invoice_id);
    //         $customerName = $invoice->customer->customer_name ?? '-';
    //     } else {
    //         $invoice = \App\Models\ImportInvoice::with('customer')->findOrFail($request->invoice_id);
    //         $customerName = $invoice->customer->customer_name ?? '-';
    //     }

    //     // Insert recovery record (without 'customer' column)
    //     $id = DB::table('invoice_recoveries')->insertGetId([
    //         'invoice_id'      => $request->invoice_id,
    //         'invoice_type'    => $request->invoice_type,
    //         'recovery_person' => $request->recovery_person,
    //         'receiving_path'  => $request->receiving_path,
    //         'recovery_amount' => $request->recovery_amount,
    //         'remarks'         => $request->remarks,
    //         'insert_by'       => auth()->user()->name ?? 'SYSTEM',
    //         'created_at'      => now(),
    //         'updated_at'      => now()
    //     ]);

    //     return response()->json([
    //         'success'        => true,
    //         'insert_by'      => auth()->user()->name ?? 'SYSTEM',
    //         'insert_datetime'=> now()->format('d-M-Y H:i:s'),
    //         'customer_name'  => $customerName,
    //         'invoice_no'     => $invoice->invoice_no
    //     ]);
    // }

    public function invoiceRecovery(Request $request)
    {
        $minBalance = $request->get('min_balance', 0); 

        $exportInvoices = ExportInvoice::with(['customer', 'items'])->get();
        $importInvoices = ImportInvoice::with(['customer', 'items'])->get();

        $invoices = collect();

        // Export invoices
        foreach ($exportInvoices as $invoice) {
            $invoice_amount = $invoice->items->sum('amount');
            $recovered_amount = DB::table('invoice_recoveries')
                ->where('invoice_ref_id', $invoice->id) // 👈 UPDATED
                ->where('invoice_type', 'export')
                ->sum('recovery_amount');

            $balance = $invoice_amount - $recovered_amount;

            if ($balance >= $minBalance) {
                $invoices->push((object)[
                    'id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'invoice_date' => $invoice->invoice_date,
                    'pay_due_date' => $invoice->pay_due_date,
                    'customer' => $invoice->customer->customer_name ?? '-',
                    'product' => 'Export',
                    'invoice_amount' => $invoice_amount,
                    'recovered_amount' => $recovered_amount,
                    'balance' => $balance,
                    'type' => 'export',
                ]);
            }
        }

        // Import invoices
        foreach ($importInvoices as $invoice) {
            $invoice_amount = $invoice->items->sum('amount');
            $recovered_amount = DB::table('invoice_recoveries')
                ->where('invoice_ref_id', $invoice->id) // 👈 UPDATED
                ->where('invoice_type', 'import')
                ->sum('recovery_amount');

            $balance = $invoice_amount - $recovered_amount;

            if ($balance >= $minBalance) {
                $invoices->push((object)[
                    'id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'invoice_date' => $invoice->invoice_date,
                    'pay_due_date' => $invoice->pay_due_date,
                    'customer' => $invoice->customer->customer_name ?? '-',
                    'product' => 'Import',
                    'invoice_amount' => $invoice_amount,
                    'recovered_amount' => $recovered_amount,
                    'balance' => $balance,
                    'type' => 'import',
                ]);
            }
        }

        // Totals
        $totals = [
            'invoice_amount' => $invoices->sum('invoice_amount'),
            'recovered_amount' => $invoices->sum('recovered_amount'),
            'balance' => $invoices->sum('balance'),
        ];

        // Customer-wise outstanding
        $customerWise = $invoices->groupBy('customer')->map(fn($group) => $group->sum('balance'));

        return view('invoicing.recovery-index', compact('invoices', 'totals', 'customerWise'));
    }

    // Show Invoice Recovery Detail Page
   public function showRecoveryInvoice($id, $type)
    {
        if ($type === 'import') {
            $invoice = \App\Models\ImportInvoice::with('customer')->findOrFail($id);
            $invoiceItems = \DB::table('import_invoice_items')
                ->where('import_invoice_id', $invoice->id)
                ->get();
        } elseif ($type === 'export') {
            $invoice = \App\Models\ExportInvoice::with('customer')->findOrFail($id);
            $invoiceItems = \DB::table('export_invoice_items')
                ->where('export_invoice_id', $invoice->id)
                ->get();
        } else {
            abort(404, 'Invalid invoice type');
        }

        $invoiceType = $type;

        // Load recoveries only for this invoice and type
        $previousRecoveries = \DB::table('invoice_recoveries')
            ->where('invoice_ref_id', $invoice->id)
            ->where('invoice_type', $invoiceType)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) use ($invoice) {
                $r->invoice_no = $invoice->invoice_no;
                $r->customer_name = $invoice->customer->customer_name ?? '-';
                return $r;
            });

        return view('invoicing.invoice-recovery-detail', compact('invoice', 'invoiceItems', 'previousRecoveries'));
    }

     public function saveRecovery(Request $request)
    {
        $request->validate([
            'invoice_ref_id' => 'required',
            'invoice_type'   => 'required|in:import,export',
            'recovery_person'=> 'required|string|max:255',
            'receiving_path' => 'required|string|max:255',
            'recovery_amount'=> 'required|numeric|min:0',
            'remarks'        => 'nullable|string|max:500',
        ]);

        $invoiceType = $request->invoice_type;

        // Make sure invoice exists
        if ($invoiceType === 'import') {
            $invoice = ImportInvoice::findOrFail($request->invoice_ref_id);
        } else {
            $invoice = ExportInvoice::findOrFail($request->invoice_ref_id);
        }

        $recovery = InvoiceRecovery::create([
            'invoice_ref_id' => $invoice->id,
            'invoice_type'   => $invoiceType,
            'recovery_person'=> $request->recovery_person,
            'receiving_path' => $request->receiving_path,
            'recovery_amount'=> $request->recovery_amount,
            'remarks'        => $request->remarks,
            'insert_by'      => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'recovery' => [
                'invoice_no' => $invoice->invoice_no,
                'customer_name' => $invoice->customer->customer_name ?? '-',
                'recovery_person' => $recovery->recovery_person,
                'recovery_amount' => $recovery->recovery_amount,
                'remarks' => $recovery->remarks,
                'inserted_by_name' => Auth::user()->name ?? '-',
                'created_at' => $recovery->created_at->format('d-M-Y H:i:s'),
            ]
        ]);
    }
 

    public function exportPrintPDF($id)
    {
        // Eager load items' bookings along with customer
        $invoice = ExportInvoice::with(['items.booking', 'customer'])->findOrFail($id);
    
        $pdf = PDF::loadView('invoicing.export-print', compact('invoice'));
    
        // Open in browser
        return $pdf->stream('Export-Invoice-' . $invoice->invoice_no . '.pdf');
    }

    public function uninvoicedExport()
    {
        $invoicedBookNos = ExportInvoiceItem::pluck('book_no')
                        ->map(fn($b) => trim((string)$b)) // remove spaces
                        ->filter() // remove null / empty
                        ->toArray();

        $bookings = Booking::with('customer')
            ->where('bookingType', 'export')
            ->whereNotNull('bookNo')
            ->whereNotIn(DB::raw('TRIM(bookNo)'), $invoicedBookNos)
            ->latest()
            ->paginate(50);

        $monthWise = Booking::selectRaw("DATE_FORMAT(bookDate, '%b-%Y') as month, COUNT(*) as total")
            ->where('bookingType', 'export')
            ->whereNotIn('bookNo', $invoicedBookNos)
            ->groupBy('month')
            ->orderByRaw("STR_TO_DATE(month, '%b-%Y')")
            ->pluck('total', 'month');

        $customerWise = Booking::selectRaw("customers.customer_name as customer, COUNT(*) as total")
            ->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->where('bookingType', 'export')
            ->whereNotIn('bookNo', $invoicedBookNos)
            ->groupBy('customers.customer_name')
            ->pluck('total', 'customer');

        return view('invoicing.un-invoice-export', compact('bookings', 'monthWise', 'customerWise'));
    }

    // public function recoveredInvoices()
    // {
    //     $recoveries = DB::table('invoice_recoveries as r')
    //         ->leftJoin('export_invoices as ei', function ($join) {
    //             $join->on('r.invoice_ref_id', '=', 'ei.id')
    //                  ->where('r.invoice_type', '=', 'export');
    //         })
    //         ->leftJoin('import_invoices as ii', function ($join) {
    //             $join->on('r.invoice_ref_id', '=', 'ii.id')
    //                  ->where('r.invoice_type', '=', 'import');
    //         })
    //         ->leftJoin('customers as c', function ($join) {
    //             $join->on('c.id', '=', DB::raw('COALESCE(ei.customer_id, ii.customer_id)'));
    //         })
    //         ->select([
    //             DB::raw('COALESCE(ei.invoice_no, ii.invoice_no) as invoice_no'),
    //             DB::raw('COALESCE(ei.invoice_date, ii.invoice_date) as invoice_date'),
    //             'c.customer_name',
    //             'r.recovery_person',
    //             'r.recovery_amount',
    //             'r.remarks',
    //             'r.receiving_path',
    //             'r.insert_by',
    //             'r.created_at',
    //             'r.invoice_type',
    //         ])
    //         ->orderBy('r.created_at', 'desc')
    //         ->get();

    //     return view('invoicing.recovered-invoices', compact('recoveries'));
    // }
        public function recoveredInvoices()
    {
        $recoveries = DB::table('invoice_recoveries as r')
            ->leftJoin('export_invoices as ei', function ($join) {
                $join->on('r.invoice_ref_id', '=', 'ei.id')
                     ->where('r.invoice_type', '=', 'export');
            })
            ->leftJoin('import_invoices as ii', function ($join) {
                $join->on('r.invoice_ref_id', '=', 'ii.id')
                     ->where('r.invoice_type', '=', 'import');
            })
            ->leftJoin('customers as c', function ($join) {
                $join->on('c.id', '=', DB::raw('COALESCE(ei.customer_id, ii.customer_id)'));
            })
            ->leftJoin('users as u', 'u.id', '=', 'r.insert_by') // Join users table
            ->select([
                DB::raw('COALESCE(ei.invoice_no, ii.invoice_no) as invoice_no'),
                DB::raw('COALESCE(ei.invoice_date, ii.invoice_date) as invoice_date'),
                'c.customer_name',
                'r.recovery_person',
                'r.recovery_amount',
                'r.remarks',
                'r.receiving_path',
                'r.insert_by',
                'u.name as inserted_by', // Get user's name instead of ID
                'r.created_at',
                'r.invoice_type',
            ])
            ->orderBy('r.created_at', 'desc')
            ->paginate(10); // Use paginate instead of get for Blade links
            
        return view('invoicing.recovered-invoices', compact('recoveries'));
    }

    public function createFromBooking($bookNo)
    {
        $booking = Booking::where('bookNo', $bookNo)->firstOrFail();
        $invoice_no = $this->generateInvoiceNumber(); // Generate automatically
        $invoiceData = [
            'book_no' => $booking->bookNo,
            'currency' => 'PKR',
            'conversion_rate' => 1,
            'rate' => 0,
            'sale_amount' => 0,
            'remarks' => $booking->remarks,
        ];

        // Single view for import & export
        return view('create-invoice', compact('invoiceData', 'booking','invoice_no'));
    }

    public function storeFromBooking(Request $request)
{
    $request->validate([
        'book_no' => 'required|string',
        'booking_type' => 'required|string|in:import,export',
        'invoice_date' => 'required|date',
        'ref_no' => 'nullable|string|max:255',
        'currency' => 'nullable|string',
        'conversion_rate' => 'nullable|numeric',
        'rate' => 'nullable|numeric',
        'sale_amount' => 'nullable|numeric',
        'freight_rate' => 'nullable|numeric',
        'account_head' => 'nullable|string|max:255',
        'remarks' => 'nullable|string',
        'pay_mode' => 'nullable|string|max:50',
    ]);

    $booking = Booking::where('bookNo', $request->book_no)->firstOrFail();

    try {
        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        if ($request->booking_type == 'import') {
            $invoice = ImportInvoice::create([
                'invoice_no'   => $invoiceNumber,
                'customer_id'  => $booking->customer_id,
                'invoice_date' => $request->invoice_date,
                'pay_due_date' => $request->invoice_date,
                'pay_mode'     => $request->pay_mode ?? null,
                'remarks'      => $request->remarks,
            ]);

            $invoice->items()->create([
                'book_no'       => $booking->bookNo,
                'shipper'       => $booking->shipperName,
                'account_head'  => $request->account_head ?? null,
                'currency'      => $request->currency ?? 'PKR',
                'currency_rate' => $request->conversion_rate ?? 1,
                'gross_weight'  => $booking->weight ?? 0,
                'rate'          => $request->rate ?? 0,
                'amount'        => $request->sale_amount ?? 0,
                'freight_rate'  => $request->freight_rate ?? 0,
                'ref_no'        => $request->ref_no ?? null,
            ]);

        } elseif ($request->booking_type == 'export') {
            $invoice = ExportInvoice::create([
                'invoice_no'   => $invoiceNumber,
                'customer_id'  => $booking->customer_id,
                'invoice_date' => $request->invoice_date,
                'pay_due_date' => $request->invoice_date,
                'pay_mode'     => $request->pay_mode ?? null,
                'remarks'      => $request->remarks,
            ]);

            $invoice->items()->create([
                'book_no'       => $booking->bookNo,
                'consignee'     => $booking->consigneeName,
                'account_head'  => $request->account_head ?? null,
                'currency'      => $request->currency ?? 'PKR',
                'currency_rate' => $request->conversion_rate ?? 1,
                'gross_weight'  => $booking->weight ?? 0,
                'rate'          => $request->rate ?? 0,
                'amount'        => $request->sale_amount ?? 0,
                'freight_rate'  => $request->freight_rate ?? 0,
                'ref_no'        => $request->ref_no ?? null,
            ]);
        }

        return redirect()->route('shipment.sale')->with('success', "Invoice {$invoiceNumber} created successfully!");
    } catch (\Exception $e) {
        return back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
    }
}



    public function allBookings(Request $request)
    {
        $query = DB::table('bookings')
            ->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'bookings.salesPerson', '=', 'users.id')
            ->select(
                'bookings.bookNo as track_no',
                'bookings.bookDate',
                'customers.customer_name as customer',
                'bookings.bookingType as product', // 👈 here bookingType will show as Product
                'bookings.origin',
                'bookings.destination',
                'bookings.weight as wtt',
                'bookings.pieces as pcs',
                'users.name as sales_person'

            );

        // Optional search by Track No or Customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bookings.bookNo', 'like', "%{$search}%")
                  ->orWhere('customers.customer_name', 'like', "%{$search}%");
            });
        }

        $bookings = $query->orderBy('bookings.created_at', 'desc')->paginate(50);

        return view('shipment-sale', compact('bookings'));
    }

    public function getCustomerExportBookings($customerId)
    {
        // Already invoiced book numbers for export invoices
        $invoicedBookNos = ExportInvoiceItem::whereNotNull('book_no')
                                            ->pluck('book_no')
                                            ->map(fn($b) => (string)$b)
                                            ->toArray();
    
        // Fetch export bookings of this customer which are NOT invoiced yet
        $bookings = Booking::where('customer_id', $customerId)
                           ->where('bookingType', 'export')  // exact spelling
                           ->whereNotNull('bookNo')
                           ->whereNotIn('bookNo', $invoicedBookNos) // exclude already invoiced
                           ->get();
    
        return response()->json($bookings);
    }

    public function getCustomerBookings(Customer $customer)
    {
        $items = DB::table('export_invoice_items')
            ->join('export_invoices', 'export_invoice_items.export_invoice_id', '=', 'export_invoices.id')
            ->where('export_invoices.customer_id', $customer->id)
            ->select('export_invoice_items.book_no','export_invoice_items.consignee','export_invoice_items.account_head','export_invoice_items.currency','export_invoice_items.currency_rate','export_invoice_items.gross_weight','export_invoice_items.rate','export_invoice_items.amount','export_invoice_items.freight_rate')
            ->get();
    
        return response()->json($items);
    }

    public function getCustomerImportBookings(Customer $customer)
    {
        $items = DB::table('import_invoice_items')
            ->join('import_invoices', 'import_invoice_items.import_invoice_id', '=', 'import_invoices.id')
            ->where('import_invoices.customer_id', $customer->id)
            ->select(
                'import_invoice_items.book_no',
                'import_invoice_items.shipper',
                'import_invoice_items.account_head',
                'import_invoice_items.currency',
                'import_invoice_items.currency_rate',
                'import_invoice_items.gross_weight',
                'import_invoice_items.rate',
                'import_invoice_items.amount',
                'import_invoice_items.freight_rate'
            )
            ->get();
            
        return response()->json($items);
    }

    private function generateInvoiceNumber()
    {
        // Get last invoice from both tables
        $lastExport = DB::table('export_invoices')->latest('id')->first();
        $lastImport = DB::table('import_invoices')->latest('id')->first();

        $lastNumber = 0;

        if ($lastExport) {
            preg_match('/\d+$/', $lastExport->invoice_no, $matches);
            $lastNumber = max($lastNumber, $matches[0] ?? 0);
        }

        if ($lastImport) {
            preg_match('/\d+$/', $lastImport->invoice_no, $matches);
            $lastNumber = max($lastNumber, $matches[0] ?? 0);
        }

        $nextNumber = $lastNumber + 1;

        return 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT); // e.g., INV-00001
    }
}
