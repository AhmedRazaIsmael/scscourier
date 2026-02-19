<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::get('/bookings', [DimensionalWeightController::class, 'getBookings'])->name('api.bookings');
Route::get('booking/{trackNumber}', [BookingController::class, 'trackBooking']);


Route::post('/connect-app', [LoginController::class, 'connect']);

Route::middleware(['shopify.session'])->group(function () {

    Route::get('/orders', [\App\Http\Controllers\Shopify\OrderController::class, 'getOrders']);

    Route::post('/push-orders', [\App\Http\Controllers\Shopify\OrderController::class, 'pushOrders']);

});
