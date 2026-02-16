<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'cities';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'country_id',
        'state_id',
        'state_code',
        'country_code',
        'latitude',
        'longitude',
        'population',
    ];

    /**
     * Relationships
     */

    // Each city belongs to a country
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    // Each city belongs to a state (or province)
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
