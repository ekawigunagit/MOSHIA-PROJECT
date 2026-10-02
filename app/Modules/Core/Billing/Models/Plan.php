<?php

namespace App\Modules\Core\Billing\Models;

use App\Modules\Core\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    protected $table = 'core_plans';

    protected $fillable = ['product_id', 'name', 'description'];

    protected $attributes = ['status' => 'draft'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
