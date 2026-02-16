<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class DimensionalWeightController extends Controller
{
    /**
     * Display the Dimensional Weight page with bookings.
     */
    public function index()
    {
        // Fetch bookings with customer relationship, latest first, paginated
        $bookings = Booking::with('customer')->latest()->paginate(50);

        // Pass to Blade
        return view('edit-dimensional-weight', compact('bookings'));
    }

    /**
     * Update Dimensional Weight for a booking (via form submission)
     */
    public function updateDimWeight(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        // Only admin or first-time edit
        if (!$user || (!$user->is_admin && $booking->was_edited)) {
            return redirect()->back()->with('error', 'You can only edit once. Contact admin for changes.');
        }

        // Validate inputs
        $request->validate([
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'dimensionalWeight' => 'nullable|numeric|min:0',
        ]);

        // Update booking
        $booking->update([
            'weight' => $request->weight ?? $booking->weight,
            'length' => $request->length ?? $booking->length,
            'width' => $request->width ?? $booking->width,
            'height' => $request->height ?? $booking->height,
            'dimensionalWeight' => $request->dimensionalWeight ?? $booking->dimensionalWeight,
            'was_edited' => true,
        ]);

        return redirect()->back()->with('success', 'Dimensional weight updated successfully.');
    }
}
