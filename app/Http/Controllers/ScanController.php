<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Scan;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\BookingStatus;

class ScanController extends Controller
{
    public function showScanForm($type, Request $request)
    {
        $validTypes = ['arrival', 'delivery'];
        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $request->validate([
            'bookNo' => 'nullable|string|max:50',
        ]);

        $scans = collect(); // empty by default

        if ($request->filled('bookNo')) {
            $bookNo = $request->bookNo;

            // Fetch booking
            $booking = Booking::with('destinationHub')->where('bookNo', $bookNo)->first();

            if (!$booking) {
                return back()->with('error', 'Book No not found in bookings table.');
            }

            // Determine new status
            $newStatus = $type === 'arrival' ? 'Arrived at Box Facility' : 'Delivered';

            // For delivery scan, check if arrival exists
            if ($type === 'delivery') {
                $arrivalScan = Scan::where('book_no', $bookNo)
                                   ->where('scan_type', 'arrival')
                                   ->first();
                if (!$arrivalScan) {
                    $newStatus = 'Out of Delivery';
                }
            }

            // Prevent double scanning
            $existingScan = Scan::where('book_no', $bookNo)
                                ->where('scan_type', $type)
                                ->first();
            if ($existingScan) {
                return back()->with('error', 'This booking has already been scanned for ' . $type);
            }

            // Update booking status
            $booking->update(['status' => $newStatus]);

            BookingStatus::updateOrCreate(
                ['booking_id' => $bookNo],
                [
                    'status' => $newStatus,
                    'description' => "Updated via $type scan",
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]
            );

            // Create new scan
            Scan::create([
                'book_no'   => $bookNo,
                'scan_type' => $type,
                'hub_code'  => $booking->destinationHub->code ?? $booking->destination,
                'scanned_by'=> auth()->id(),
                'status'    => $newStatus,
            ]);

            // Fetch scans for this bookNo with only required relationships
            $scans = Scan::with(['user', 'booking.destinationHub', 'latestBookingStatus'])
                         ->where('scan_type', $type)
                         ->where('book_no', $bookNo)
                         ->latest()
                         ->paginate(10);
        }

        return view('scans.scan-form', [
            'scans' => $scans,
            'type' => $type,
        ]);
    }
}
