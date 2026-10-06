<?php

namespace App\Modules\Wedding\Models;

use App\Modules\Core\Billing\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $table = 'wedding_invitations';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'draft_content' => 'array', 'published_content' => 'array',
            'first_published_at' => 'immutable_datetime', 'published_at' => 'immutable_datetime',
        ];
    }

    public function publishedOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'published_order_id');
    }
}
