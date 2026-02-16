<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';
    protected $fillable = ['name', 'translations', 'flag', 'wikiDataId'];

    public function subregions()
    {
        return $this->hasMany(Subregion::class, 'region_id');
    }

    public function countries()
    {
        return $this->hasMany(Country::class, 'region_id');
    }
}
