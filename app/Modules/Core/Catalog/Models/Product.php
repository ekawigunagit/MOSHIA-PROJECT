<?php

namespace App\Modules\Core\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'core_products';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = ['title', 'icon', 'summary', 'description', 'status', 'sort_order'];

    protected function casts(): array
    {
        return ['id' => 'integer', 'phase' => 'integer', 'sort_order' => 'integer'];
    }

    public function catalogData(): array
    {
        return $this->only(['id', 'slug', 'title', 'icon', 'summary', 'description', 'status', 'phase', 'sort_order']);
    }
}
