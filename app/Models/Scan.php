<?php
namespace App\Models;
use App\Models\City;
use App\Models\User;
use App\Models\Booking;
use App\Models\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Scan extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_no',
        'hub_code',
        'scan_type',
        'status',
        'scanned_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function hub()
    {
        return $this->belongsTo(City::class, 'hub_code', 'code');
    }
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'book_no', 'bookNo'); 
    }
    public function latestBookingStatus()
    {
        return $this->hasOne(BookingStatus::class, 'booking_id', 'book_no')
                    ->latestOfMany('updated_at');
    }
}
