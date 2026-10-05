<?php

namespace App\Modules\Core\Billing\Models;

use App\Modules\Core\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    protected $table = 'core_plans';

    protected $fillable = ['product_id', 'name', 'description', 'commercial_terms'];

    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return ['commercial_terms' => 'array'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
