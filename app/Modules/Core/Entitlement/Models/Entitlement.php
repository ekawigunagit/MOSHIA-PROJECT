<?php

namespace App\Modules\Core\Entitlement\Models;

use Illuminate\Database\Eloquent\Model;

class Entitlement extends Model
{
    protected $table = 'core_entitlements';

    protected $fillable = ['tenant_id', 'product_slug', 'status', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }
}
