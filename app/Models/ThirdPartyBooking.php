<?php

namespace App\Models;

use App\Models\User;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThirdPartyBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_no',
        'book_date',
        'company_name',
        'ref_no',
        'remarks',
        'updated_by'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shipper()
    {
        return $this->belongsTo(Shipper::class); // or User::class depending
    }

    public function consignee()
    {
        return $this->belongsTo(Consignee::class); // or User::class depending
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'book_no', 'bookNo');
    }
}
