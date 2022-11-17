<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextBlock extends Model
{
    use HasFactory;
    protected $table = "textblocks";
    protected $primaryKey = "id";
    protected $fillable = ['id','block'];
}
