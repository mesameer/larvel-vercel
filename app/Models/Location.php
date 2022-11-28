<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Location extends Model
{
    use HasFactory;
    use Sortable;

    // public $sortable = ['country', 'state_name', 'county_name', 'city_name', 'zip_code', 'zip_population'];
    public $sortableAs = ['country', 'state_name', 'county_name', 'city_name', 'zip_code', 'zip_population'];
}
