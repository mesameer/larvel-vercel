<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationDetail extends Model
{
    use HasFactory;
    protected $table = "location_detail";
    protected $primaryKey = "id";
    protected $fillable = ['approved','country','state_name','state_code','county','city','zip','state_latitude','state_longitude','county_latitude','county_longitude','city_latitude','city_longitude','zip_latitude','zip_longitude','timezone','areacode','zip_population','type'];
}
