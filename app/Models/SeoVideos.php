<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoVideos extends Model
{
    use HasFactory;
    protected $table = "seo_videos";
    protected $primaryKey = "id";
}
