<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'org_form', 'org_number', 'address', 'latitude', 'longitude'
    ];

    public function scopeWithAddressLocation($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }
}
