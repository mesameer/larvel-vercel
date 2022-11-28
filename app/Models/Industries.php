<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Industries extends Model
{
    use HasFactory;
    use Sortable;

    public $sortableAs = ['industry_name', 'created_at'];
}
