<?php
namespace App\Modules\Products\Wedding\Services;
use App\Modules\Core\Billing\Models\PurchaseOrder;
class WeddingFeatures {
    public static function videoHeader(PurchaseOrder $order): bool {
        return in_array($order->terms['key'] ?? null, ['emerald', 'diamond'], true)
            && ($order->terms['video_header_request'] ?? false);
    }
}
