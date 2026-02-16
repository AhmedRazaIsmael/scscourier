<?php
namespace App\Models;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;

class BookingStatus extends Model
{
    protected $fillable = ['booking_id', 'status', 'description','created_at', 'updated_by'];


    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
