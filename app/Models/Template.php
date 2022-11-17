<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;
    protected $table = "template";
    protected $primaryKey = "id";
    protected $fillable = ['type','meta_title','meta_heading','meta_description','html'];
}
