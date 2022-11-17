<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomTag extends Model
{
    use HasFactory;
    protected $table = "custom_tag";
    protected $primaryKey = "id";
    protected $fillable = ['tagName','description'];
}
