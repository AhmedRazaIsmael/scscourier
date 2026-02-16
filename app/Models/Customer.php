<?php

namespace App\Models;

use App\Models\City;
use App\Models\Country;
use App\Models\ImportInvoice;
use App\Models\CustomerAttachment;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'customer_name',
        'contact_person_1',
        'contact_no_1',
        'email_1',
        'address_1',
        'contact_person_2',
        'contact_no_2',
        'email_2',
        'address_2',
        'ntn',
        'website',
        'open_date',
        'parent_customer_code',
        'sales_person',
        'product',
        'tariff_code',
        'territory',
        'country_id',
        'city_id',
        'status',
        'business_type',
        'nic',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function attachments()
    {
        return $this->hasMany(CustomerAttachment::class);
    }
    public function importInvoices()
    {
        return $this->hasMany(ImportInvoice::class);
    }

}
