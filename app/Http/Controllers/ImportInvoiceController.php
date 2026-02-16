<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\ImportInvoice;
use App\Models\ImportInvoiceItem;
use Illuminate\Support\Facades\DB;

class ImportInvoiceController extends Controller
{
    /**
     * Show create form for import invoice
     */
    public function create()
    {
        $customers = Customer::all();
        $invoice_no = $this->generateInvoiceNumber(); // Auto-generate
        return view('invoicing.import-create', compact('customers', 'invoice_no'));
    }

    /**
     * Store new import invoice
     */
   public function store(Request $request)
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'invoice_date' => 'required|date',
        'pay_due_date' => 'required|date',
        'pay_mode'     => 'nullable|string|max:50',
        'remarks'      => 'nullable|string',
        'items'        => 'required|array|min:1',
    ]);

    DB::beginTransaction();

    try {
        // Generate invoice number starting with INV-
        $invoiceNumber = $this->generateInvoiceNumber();

        // Create main invoice
        $invoice = ImportInvoice::create([
            'invoice_no'   => $invoiceNumber,
            'customer_id'  => $request->customer_id,
            'invoice_date' => $request->invoice_date,
            'pay_due_date' => $request->pay_due_date,
            'pay_mode'     => $request->pay_mode,
            'remarks'      => $request->remarks,
        ]);

        // Create items
        foreach ($request->items as $item) {
            if (!isset($item['book_no'])) continue;

            $booking = Booking::where('bookNo', $item['book_no'])->first();

            $invoice->items()->create([
                'book_no'       => $item['book_no'],
                'account_head'  => $item['account_head'] ?? null,
                'currency'      => $item['currency'] ?? null,
                'currency_rate' => is_numeric($item['currency_rate']) ? $item['currency_rate'] : 0,
                'gross_weight'  => is_numeric($item['gross_weight']) ? $item['gross_weight'] : 0,
                'rate'          => is_numeric($item['rate']) ? $item['rate'] : 0,
                'amount'        => is_numeric($item['amount']) ? $item['amount'] : 0,
                'freight_rate'  => is_numeric($item['freight_rate']) ? $item['freight_rate'] : 0,
            ]);
        }

        DB::commit();

        return redirect()->route('invoice.import')->with('success', "Import Invoice {$invoiceNumber} created successfully.");
    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
    }
}

    /**
     * Show single invoice JSON
     */
    public function show($id)
    {
        $invoice = ImportInvoice::with('items')->findOrFail($id);
        return response()->json($invoice);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $invoice = ImportInvoice::with('items', 'customer')->findOrFail($id);
        // dd($invoice->items);
        return view('invoicing.import-edit', compact('invoice'));
    }

    /**
     * Update invoice and items
     */
    public function update(Request $request, $id)
    {
        $invoice = ImportInvoice::with('items')->findOrFail($id);

        $request->validate([
            'invoice_date' => 'required|date',
            'pay_due_date' => 'required|date',
            'pay_mode'     => 'nullable|string',
            'remarks'      => 'nullable|string',
            'items'        => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Update main invoice
            $invoice->update($request->only(['invoice_date','pay_due_date','pay_mode','remarks']));

            // Update or create items
            foreach ($request->items as $item) {
                if (!isset($item['book_no'])) continue;

                $booking = Booking::where('bookNo', $item['book_no'])->first();

                if (isset($item['id'])) {
                    // Update existing item
                    $invoice->items()->where('id', $item['id'])->update([
                        'book_no'       => $item['book_no'] ?? null,
                        'shipper'       => $booking->shipperName ?? $item['shipper'] ?? null,
                        'consignee'     => $booking->consigneeName ?? null,
                        'account_head'  => $item['account_head'] ?? null,
                        'currency'      => $item['currency'] ?? null,
                        'currency_rate' => is_numeric($item['currency_rate']) ? $item['currency_rate'] : 0,
                        'gross_weight'  => is_numeric($item['gross_weight']) ? $item['gross_weight'] : 0,
                        'rate'          => is_numeric($item['rate']) ? $item['rate'] : 0,
                        'amount'        => is_numeric($item['amount']) ? $item['amount'] : 0,
                        'freight_rate'  => is_numeric($item['freight_rate']) ? $item['freight_rate'] : 0,
                    ]);
                } else {
                    // Create new item
                    $invoice->items()->create([
                        'book_no'       => $item['book_no'] ?? null,
                        'shipper'       => $booking->shipperName ?? $item['shipper'] ?? null,
                        'consignee'     => $booking->consigneeName ?? null,
                        'account_head'  => $item['account_head'] ?? null,
                        'currency'      => $item['currency'] ?? null,
                        'currency_rate' => is_numeric($item['currency_rate']) ? $item['currency_rate'] : 0,
                        'gross_weight'  => is_numeric($item['gross_weight']) ? $item['gross_weight'] : 0,
                        'rate'          => is_numeric($item['rate']) ? $item['rate'] : 0,
                        'amount'        => is_numeric($item['amount']) ? $item['amount'] : 0,
                        'freight_rate'  => is_numeric($item['freight_rate']) ? $item['freight_rate'] : 0,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Invoice updated successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Print view
     */
    public function print($id)
    {
        $invoice = ImportInvoice::with('items', 'customer')->findOrFail($id);
        return view('invoicing.import-print', compact('invoice'));
    }

    /**
     * Print PDF
     */
    public function printPDF($id)
    {
        $invoice = ImportInvoice::with(['items.booking', 'customer'])->findOrFail($id);
    
        $pdf = PDF::loadView('invoicing.import-print', compact('invoice'));
        return $pdf->stream('Import-Invoice-' . $invoice->invoice_no . '.pdf');
    }

    /**
     * Uninvoiced bookings
     */
    // public function getCustomerBookings($customerId)
    // {
    //     // Get all book numbers already invoiced
    //     $invoicedBookNos = ImportInvoiceItem::pluck('book_no')->toArray();

    //     // Fetch bookings of the customer that are import type and not yet invoiced
    //     $bookings = Booking::where('customer_id', $customerId)
    //                        ->where('bookingType', 'import')
    //                        ->whereNotIn('bookNo', $invoicedBookNos)
    //                        ->get();

    //     return response()->json($bookings);
    // }

    public function getCustomerBookings($customerId)
    {
        // Already invoiced book numbers for import invoices
        $invoicedBookNos = ImportInvoiceItem::whereNotNull('book_no')
                                            ->pluck('book_no')
                                            ->map(fn($b) => (string)$b)
                                            ->toArray();

        // Fetch import bookings of this customer which are NOT invoiced yet
        $bookings = Booking::where('customer_id', $customerId)
                           ->where('bookingType', 'import')  // exact spelling
                           ->whereNotNull('bookNo')
                           ->whereNotIn('bookNo', $invoicedBookNos) // exclude already invoiced
                           ->get();

        return response()->json($bookings);
    }

    public function uninvoicedImport(Request $request)
    {
        $invoicedBookNos = ImportInvoiceItem::pluck('book_no')->toArray();

        $bookings = Booking::with('customer')
            ->where('bookingType', 'import')
            ->whereNotIn('bookNo', $invoicedBookNos)
            ->latest()
            ->paginate(50);

        return view('invoicing.un-invoice-import', compact('bookings'));
    }
    public function updateItems(Request $request, $id)
    {
        $invoice = ImportInvoice::findOrFail($id);
    
        foreach ($request->items as $itemData) {
            $item = $invoice->items()->find($itemData['id']);
            if ($item) {
                $item->update($itemData);
            }
        }
    
        return redirect()->back()->with('success', 'Invoice items updated successfully.');
    }

    public function createFromBooking($bookNo)
    {
        $booking = Booking::where('bookNo', $bookNo)->firstOrFail();
    
        $invoice = [
            'book_no'       => $booking->bookNo,
            'currency'      => 'PKR',
            'currency_rate' => 1,
            'rate'          => 0,
            'sale_amount'   => 0,
        ];
    
        return view('invoicing.import-create-from-booking', compact('invoice', 'booking'));
    }
    
  public function storeFromBooking(Request $request)
{
    $request->validate([
        'book_no' => 'required|exists:bookings,bookNo',
        'invoice_date' => 'required|date',
        'currency' => 'required|in:PKR,USD',
        'conversion_rate' => 'required|numeric|min:0.01',
        'rate' => 'required|numeric|min:0',
        'sale_amount' => 'required|numeric|min:0',
        'pay_mode' => 'nullable|string|max:50',
        'remarks' => 'nullable|string|max:255',
        'ref_no' => 'nullable|string|max:255',
    ]);

    DB::beginTransaction();
    try {
        $booking = Booking::where('bookNo', $request->book_no)->firstOrFail();

        // Generate invoice number automatically
        $invoiceNumber = $this->generateInvoiceNumber();

        // Create or get invoice for this booking & date
        $invoice = ImportInvoice::firstOrCreate(
            [
                'customer_id' => $booking->customer_id,
                'invoice_date' => $request->invoice_date,
                'pay_due_date' => $request->invoice_date,
            ],
            [
                'invoice_no' => $invoiceNumber,
                'pay_mode' => $request->pay_mode ?? null,
                'remarks' => $request->remarks ?? null,
            ]
        );

        $invoice->items()->create([
            'book_no' => $request->book_no,
            'ref_no' => $request->ref_no ?? null,
            'currency' => $request->currency,
            'currency_rate' => $request->conversion_rate,
            'rate' => $request->rate,
            'sale_amount' => $request->sale_amount,
            'remarks' => $request->remarks ?? null,
        ]);

        DB::commit();
        return redirect()->route('invoice.import')->with('success', "Invoice {$invoiceNumber} created successfully.");
    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', $e->getMessage())->withInput();
    }
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
