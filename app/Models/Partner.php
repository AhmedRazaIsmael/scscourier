<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
