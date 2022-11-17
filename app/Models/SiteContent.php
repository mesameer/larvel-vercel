<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    use HasFactory;
    protected $table = "site_content";
    protected $primaryKey = "id";
    protected $fillable = ['service_id','type','location','text_block_1','text_block_2','text_block_3','text_block_4','meta_heading','meta_title','meta_description','approved'];
}
