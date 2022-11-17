<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoService extends Model
{
    use HasFactory;
    protected $table = "seo_services";
    protected $primaryKey = "id";
}
