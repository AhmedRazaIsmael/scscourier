<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subregion extends Model
{
    protected $table = 'subregions';
    protected $fillable = ['name', 'region_id', 'translations', 'flag', 'wikiDataId'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function countries()
    {
        return $this->hasMany(Country::class, 'subregion_id');
    }
}
