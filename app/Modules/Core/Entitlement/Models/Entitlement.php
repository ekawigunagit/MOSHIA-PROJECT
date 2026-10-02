<?php

namespace App\Modules\Core\Entitlement\Models;

use App\Modules\Core\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entitlement extends Model
{
    protected $table = 'core_entitlements';

    protected $fillable = ['tenant_id', 'product_id', 'status', 'starts_at', 'ends_at'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }
}
