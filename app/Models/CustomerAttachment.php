<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerAttachment extends Model
{
    protected $fillable = [
        'customer_id',
        'file_path',
        'filename',
        'mimetype',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
