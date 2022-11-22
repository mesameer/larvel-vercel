<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;
    protected $table = "site";
    protected $primaryKey = "id";
    protected $fillable = ['id','title','description','logo','state','city','domain','latitude','longitude','service_id'];
}
