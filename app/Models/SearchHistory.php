<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kyslik\ColumnSortable\Sortable;

class SearchHistory extends Model
{
    use HasFactory;
    use Sortable;

    protected $fillable = ['is_favorite'];

    public $sortableAs = ['created_at', 'title'];

    public function toggleFavorite() {
        $this->update([
            'is_favorite' => DB::raw('NOT is_favorite')
        ]);
    }
}
