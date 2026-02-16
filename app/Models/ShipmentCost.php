<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShipmentCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'trackNo',      // matches DB column
        'date',         // add date
        'accountHead',  // must match DB column exactly
        'costDesc',
        'costAmount',
        'status',
    ];

    // Each costing belongs to a booking
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'trackNo', 'bookNo');
    }
    
}
