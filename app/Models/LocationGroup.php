<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationGroup extends Model
{
    use HasFactory;

    protected $fillable = ['group_name', 'zip_codes'];

    public function roles() {
        return $this->belongsToMany(LocationGroupRecord::class, 'location_group_id');
    }
}
