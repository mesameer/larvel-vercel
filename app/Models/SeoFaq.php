<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoFaq extends Model
{
    use HasFactory;
    protected $table = "seo_faq";
    protected $primaryKey = "id";
}
