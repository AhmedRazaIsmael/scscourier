<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\ShipmentCost;
use Illuminate\Http\Request;
use Carbon\Carbon; // ✅ Add this
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    // Display all shipments for costing
    public function shipmentCost()
    {
        $shipments = Booking::with('customer')->paginate(50);
        return view('shipment-cost', compact('shipments'));
    }

    // Show shipment costing detail for a specific bookNo
    public function showCostDetail($bookNo)
    {
        $booking = Booking::where('bookNo', $bookNo)->firstOrFail();
        $costEntries = ShipmentCost::where('trackNo', $bookNo)->get();

        return view('shipment-cost-detail', [
            'bookNo' => $booking->bookNo,           // Track No
            'bookDate' => $booking->bookDate,       // Booking date
            'customer' => optional($booking->customer)->customer_name ?? '-',
            'origin' => $booking->origin,
            'destination' => $booking->destination,
            'weight' => $booking->weight,
            'pieces' => $booking->pieces,
            'invoiceValue' => $booking->invoiceValue,
            'costEntries' => $costEntries
        ]);
    }

    // Store a new costing entry
    public function storeCost(Request $request)
    {
        $request->validate([
            'trackNo' => 'required',
            'account_head' => 'nullable',
            'costDesc' => 'nullable',
            'costAmount' => 'nullable|numeric',
            'status' => 'nullable'
        ]);

        ShipmentCost::create([
            'trackNo' => $request->trackNo,
            'accountHead' => $request->account_head, // matches DB
            'costDesc' => $request->costDesc,
            'costAmount' => $request->costAmount,
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Costing entry added successfully.');
    }

    // Show edit form
    public function editCost($id)
    {
        $entry = ShipmentCost::findOrFail($id);
        return view('shipment-cost-edit', compact('entry'));
    }

    // Update costing entry
    public function updateCost(Request $request, $id)
    {
        $entry = ShipmentCost::findOrFail($id);

        $request->validate([
            'account_head' => 'required',
            'costDesc' => 'required',
            'costAmount' => 'required|numeric',
            'status' => 'required'
        ]);

        $entry->update([
            'accountHead' => $request->account_head,
            'costDesc' => $request->costDesc,
            'costAmount' => $request->costAmount,
            'status' => $request->status
        ]);

        return redirect()->route('shipment.cost.detail', $entry->trackNo)
            ->with('success', 'Costing entry updated successfully.');
    }

    // Delete costing entry
    public function deleteCost($id)
    {
        $entry = ShipmentCost::findOrFail($id);
        $trackNo = $entry->trackNo;
        $entry->delete();

        return redirect()->route('shipment.cost.detail', $trackNo)
            ->with('success', 'Costing entry deleted successfully.');
    }

    
    
    public function dashboard(Request $request)
    {
        // Base query
        $bookingsQuery = Booking::query();

        // Filters
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = Carbon::parse($request->from_date)->startOfDay();
            $to   = Carbon::parse($request->to_date)->endOfDay();
            $bookingsQuery->whereBetween('bookDate', [$from, $to]);
        }

        if ($request->filled('product')) {
            $bookingsQuery->where('bookingType', $request->product);
        }

        if ($request->filled('customer')) {
            $bookingsQuery->where('customer_id', $request->customer);
        }

        if ($request->filled('sales_person')) {
            $bookingsQuery->where('salesPerson', $request->sales_person);
        }

        $bookings = $bookingsQuery->get();

        // Dropdowns
        $products = Booking::select('bookingType')->distinct()->pluck('bookingType');
        $customers = Customer::all();
        $salesPersons = Booking::select('salesPerson')->distinct()->pluck('salesPerson');

        // Current year
        $year = Carbon::now()->year;

        // --- Yearly aggregates with safe float ---
        $yearlyInvoice = [
            'import' => $bookings->where('bookingType', 'import')->sum(fn($b) => (float)$b->invoiceValue),
            'export' => $bookings->where('bookingType', 'export')->sum(fn($b) => (float)$b->invoiceValue),
        ];

        $yearlyRecovery = $bookings->sum(fn($b) => (float)$b->invoiceValue); // replace with actual recovery if you have table
        $yearlyExpense  = $bookings->sum(fn($b) => (float)$b->invoiceValue); // replace with expense table if exists

        // --- Monthly aggregates ---
        $months = collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->format('M'));

        $monthlyImport = $months->mapWithKeys(function($month, $idx) use ($bookings){
            $m = $idx + 1;
            $sum = $bookings->where('bookingType','import')
                            ->filter(fn($b)=> Carbon::parse($b->bookDate)->month == $m)
                            ->sum(fn($b)=> (float)$b->invoiceValue);
            return [$month => $sum];
        });

        $monthlyExport = $months->mapWithKeys(function($month, $idx) use ($bookings){
            $m = $idx + 1;
            $sum = $bookings->where('bookingType','export')
                            ->filter(fn($b)=> Carbon::parse($b->bookDate)->month == $m)
                            ->sum(fn($b)=> (float)$b->invoiceValue);
            return [$month => $sum];
        });

        $monthlyRecovery = $months->mapWithKeys(function($month, $idx) use ($bookings){
            $m = $idx + 1;
            $sum = $bookings->filter(fn($b)=> Carbon::parse($b->bookDate)->month == $m)
                            ->sum(fn($b)=> (float)$b->invoiceValue); // replace with actual recovery
            return [$month => $sum];
        });

        $monthlyExpense = $months->mapWithKeys(function($month, $idx) use ($bookings){
            $m = $idx + 1;
            $sum = $bookings->filter(fn($b)=> Carbon::parse($b->bookDate)->month == $m)
                            ->sum(fn($b)=> (float)$b->invoiceValue); // replace with actual expense
            return [$month => $sum];
        });

        // --- Product Wise Invoice ---
        $productInvoice = $bookings->groupBy('bookingType')
            ->map(fn($group)=> $group->sum(fn($b)=> (float)$b->invoiceValue));

        // --- Sales Person Wise Invoice ---
        $salesPersonInvoice = $bookings->groupBy('salesPerson')
            ->map(fn($group)=> $group->sum(fn($b)=> (float)$b->invoiceValue));

        // --- Customer Wise Invoice ---
        $customerInvoice = $bookings->groupBy(function($b){ return $b->customer->customer_name ?? '-'; })
            ->map(fn($group)=> $group->sum(fn($b)=> (float)$b->invoiceValue));

        return view('financial-dashboard', compact(
            'year', 'products', 'customers', 'salesPersons',
            'yearlyInvoice', 'yearlyRecovery', 'yearlyExpense',
            'months', 'monthlyImport', 'monthlyExport', 'monthlyRecovery', 'monthlyExpense',
            'productInvoice', 'salesPersonInvoice', 'customerInvoice'
        ));
    }
}


