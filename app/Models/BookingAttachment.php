<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingAttachment extends Model
{
    protected $fillable = ['booking_id', 'file_path', 'file_type', 'label'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}