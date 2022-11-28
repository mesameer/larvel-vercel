<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationGroupCustomZipCode extends Model
{
    use HasFactory;

    protected $fillable = ['location_group_id', 'country', 'state_name', 'county_name', 'city_name', 'zip_codes'];
}
