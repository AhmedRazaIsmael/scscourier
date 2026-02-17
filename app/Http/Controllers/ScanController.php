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

    // Clear session list if requested
    if ($request->has('clearSession')) {
        session()->forget('scanned_books_' . $type);
        return redirect('/scan/' . $type);
    }

    $request->validate([
        'bookNo' => 'nullable|string|max:50',
    ]);

    $scans = collect();

    if ($request->filled('bookNo')) {

        try {
            $rawBookNo = trim($request->bookNo);

            // Fix double-scan (AB123AB123 -> AB123)
            $len = strlen($rawBookNo);
            if ($len >= 4 && $len % 2 === 0) {
                $half = substr($rawBookNo, 0, $len / 2);
                if ($half === substr($rawBookNo, $len / 2)) {
                    $rawBookNo = $half;
                }
            }
            $bookNo = $rawBookNo;

            $booking = Booking::with('destinationHub')->where('bookNo', $bookNo)->first();

            if (!$booking) {
                session()->flash('scan_error', 'Book No ' . $bookNo . ' not found.');
            } else {

                $existingScan = Scan::where('book_no', $bookNo)
                    ->where('scan_type', $type)
                    ->first();

                if ($existingScan) {
                    session()->flash('scan_error', 'Book No ' . $bookNo . ' already scanned for ' . $type . '.');
                } else {

                    $newStatus = $type === 'arrival' ? 'Arrived at Box Facility' : 'Delivered';

                    if ($type === 'delivery') {
                        $arrivalScan = Scan::where('book_no', $bookNo)
                            ->where('scan_type', 'arrival')
                            ->first();
                        if (!$arrivalScan) {
                            $newStatus = 'Out of Delivery';
                        }
                    }

                    $booking->update(['status' => $newStatus]);

                    BookingStatus::updateOrCreate(
                        ['booking_id' => $bookNo],
                        [
                            'status'      => $newStatus,
                            'description' => "Updated via $type scan",
                            'updated_by'  => auth()->id(),
                            'updated_at'  => now(),
                        ]
                    );

                    Scan::create([
                        'book_no'    => $bookNo,
                        'scan_type'  => $type,
                        'hub_code'   => $booking->destinationHub->code ?? $booking->destination,
                        'scanned_by' => auth()->id(),
                        'status'     => $newStatus,
                    ]);

                    session()->flash('scan_success', 'Book No ' . $bookNo . ' scanned successfully!');

                    $sessionKey = 'scanned_books_' . $type;
                    $scannedList = session($sessionKey, []);
                    array_unshift($scannedList, $bookNo);
                    session([$sessionKey => $scannedList]);
                }
            }

        } catch (\Exception $e) {
            session()->flash('scan_error', 'Something went wrong: ' . $e->getMessage());
        }

        // Fetch only session scanned books
        $sessionKey = 'scanned_books_' . $type;
        $scannedBookNos = session($sessionKey, []);

        if (!empty($scannedBookNos)) {
            try {
                $scans = Scan::with(['user', 'booking.destinationHub', 'latestBookingStatus'])
                    ->where('scan_type', $type)
                    ->whereIn('book_no', $scannedBookNos)
                    ->orderByRaw(
                        'FIELD(book_no, ' . implode(',', array_fill(0, count($scannedBookNos), '?')) . ')',
                        $scannedBookNos
                    )
                    ->paginate(10);
            } catch (\Exception $e) {
                session()->flash('scan_error', 'Error fetching scan list: ' . $e->getMessage());
            }
        }
    }

    return view('scans.scan-form', [
        'scans' => $scans,
        'type'  => $type,
    ]);
}
}
