<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\BookingStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class BookingStatusController extends Controller
{
    /**
     * Show status edit page with history
     */
    public function edit($id)
    {
        $booking = Booking::with(['statuses.user'])->findOrFail($id);
        return view('edit-booking-status', compact('booking'));
    }

    /**
     * Store new status update
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:255',
            'description' => 'nullable|string',
            'statusDateTime' => 'required|date', // ✅ validate datetime
        ]);

        BookingStatus::create([
            'booking_id'  => $id,
            'status'      => $request->status,
            'description' => $request->description,
            'created_at'  => $request->statusDateTime, // ✅ use the manually entered date
            'updated_by'  => auth()->id(), // ✅ Logged in user ka ID save hoga
        ]);

        return redirect()->route('booking.status.edit', $id)
            ->with('success', 'Booking status updated successfully!');
    }

    public function bookingStatus()
    {
        $bookings = Booking::with(['customer', 'statuses' => function ($q) {
            $q->latest();
        }])->get();

        return view('booking-status', compact('bookings'));
    }

    public function updateByDate(Request $request)
    {
        $request->validate([
            'status_date' => 'required|date',
            'status'      => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $statusDate = $request->input('status_date');

        // Find existing booking statuses that match the given status_date
        $statuses = BookingStatus::whereDate('created_at', $statusDate)->get();

        if ($statuses->isEmpty()) {
            return redirect()->back()->with('error', 'No booking statuses found for this status date.');
        }

        $updatedCount = 0;

        foreach ($statuses as $status) {
            $status->update([
                'status'      => $request->status,
                'description' => $request->description,
                'updated_by'  => auth()->id() ?? 1,
                'updated_at'  => now(),
            ]);

            $updatedCount++;
        }

        return redirect()->back()->with('success', "{$updatedCount} booking statuses updated successfully.");
    }


    public function editBookingStatusView()
    {
        $bookings = Booking::with('customer')->paginate(50);

        return view('bulk-booking-status', compact('bookings'));
    }

    public function filterByDate(Request $request)
    {
        $request->validate([
            'status_date' => 'required|date',
        ]);

        $statusDate = $request->input('status_date');

        $bookings = Booking::whereHas('statuses', function ($query) use ($statusDate) {
            $query->whereDate('created_at', $statusDate);
        })
            ->with([
                'customer',
                'statuses' => function ($query) {
                    $query->latest();
                }
            ])
            ->get();

        if ($bookings->isEmpty()) {
            return redirect()->back()->with('error', 'No bookings found for the selected status date.');
        }

        return view('bulk-booking-status', compact('bookings'))->with('filterDate', $statusDate);
    }

    /** 🔍 Filter based on simple column-value conditions */
    public function filter(Request $request)
    {
        $column = $request->input('column');
        $operator = $request->input('operator', '=');
        $value = $request->input('value');

        $bookings = Booking::query();

        if ($column && $value !== null) {
            $bookings->where($column, $operator, $value);
        }

        return view('booking-status', ['bookings' => $bookings->get()]);
    }

    /** 🔍 Row filter with multiple conditions */
    public function rowFilter(Request $request)
    {
        $filters = $request->input('filters', []);
        $query = Booking::query();

        foreach ($filters as $filter) {
            $query->where($filter['column'], $filter['operator'], $filter['value']);
        }

        return view('booking-status', ['bookings' => $query->get()]);
    }

    /** 🧮 Compute expression like "weight * pieces" */
    public function compute(Request $request)
    {
        $expression = $request->input('compute_expression');

        try {
            $result = DB::table('bookings')->selectRaw("SUM($expression) as result")->first()->result ?? 0;
            return redirect()->back()->with('computeResult', $result);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Invalid expression.');
        }
    }

    /** 📊 Aggregate like SUM, AVG, COUNT on a column */
    public function aggregate(Request $request)
    {
        $column = $request->input('aggregate_column');
        $function = strtoupper($request->input('aggregate_function', 'SUM'));

        $result = DB::table('bookings')->selectRaw("$function($column) as result")->first()->result ?? 0;

        return redirect()->back()->with('aggregateResult', [
            'function' => $function,
            'column' => $column,
            'result' => $result,
        ]);
    }

    /** 📈 Generate chart data (you can connect to your chart modal) */
    public function chart(Request $request)
    {
        $column = $request->input('chart_column', 'originCountry');
        $chartType = $request->input('chart_type', 'bar');

        $data = Booking::select($column, DB::raw('count(*) as total'))
            ->groupBy($column)
            ->get();

        return redirect()->back()->with('chartData', [
            'chartType' => $chartType,
            'labels' => $data->pluck($column),
            'values' => $data->pluck('total'),
        ]);
    }

    /** ⬇️ Download bookings as CSV */
    public function download(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $query = Booking::with(['customer', 'statuses']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bookNo', 'like', "%$search%")
                    ->orWhereHas('customer', fn($q) => $q->where('customer_name', 'like', "%$search%"))
                    ->orWhere('product', 'like', "%$search%")
                    ->orWhere('origin', 'like', "%$search%")
                    ->orWhere('destination', 'like', "%$search%");
            });
        }

        $data = $query->get()->map(function ($b) {
            $latestStatus = $b->statuses->first();
            return [
                'Book No' => $b->bookNo,
                'Book Date' => $b->bookDate ? $b->bookDate->format('d-m-Y') : '-',
                'Status Date/Time' => $latestStatus?->created_at->format('d-m-Y H:i') ?? '-',
                'Track Status' => $latestStatus?->status ?? '-',
                'Customer' => $b->customer?->customer_name ?? '-',
                'Item Content' => $b->itemContent ?? '-',
                'Origin' => $b->origin ?? '-',
                'Destination' => $b->destination ?? '-',
                'Weight' => $b->weight ?? '-',
                'Pieces' => $b->pieces ?? '-',
                'Order No' => $b->orderNo ?? '-',
                'Shipper Name' => $b->shipperName ?? '-',
                'Shipper Contact No.' => $b->shipperNumber ?? '-',
                'Shipper Address' => $b->shipperAddress ?? '-',
                'Consignee Name' => $b->consigneeName ?? '-',
                'Consignee Contact No.' => $b->consigneeNumber ?? '-',
                'Consignee Address' => $b->consigneeAddress ?? '-',
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
            implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
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

        return back()->with('error', 'Unsupported download format');
    }

    public function updateSelected(Request $request)
    {
        $request->validate([
            'selected_bookings' => 'required|array|min:1',
            'status' => 'required|string|max:255',
            'status_date' => 'required|date',
        ]);

        $statusText = $request->status;
        $statusDate = Carbon::parse($request->status_date);
        $userId = Auth::id();
        $updatedCount = 0;

        foreach ($request->selected_bookings as $bookingId) {
            $booking = Booking::find($bookingId);
            if (!$booking) continue;

            $existingStatus = BookingStatus::where(function ($q) use ($booking) {
                $q->where('booking_id', $booking->id)
                    ->orWhere('booking_id', $booking->bookNo);
            })
                ->latest('created_at')
                ->first();

            if ($existingStatus) {
                $existingStatus->update([
                    'status'      => $statusText,
                    'description' => 'Updated via bulk action',
                    'updated_by'  => $userId,
                    'updated_at'  => $statusDate,
                ]);
                $updatedCount++;
            }
        }

        return $updatedCount > 0
            ? back()->with('success', "$updatedCount booking status(es) updated successfully!")
            : back()->with('error', 'No matching booking statuses found to update.');
    }
}
