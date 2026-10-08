<?php
namespace App\Modules\Wedding\Services;
use App\Modules\Wedding\Models\Invitation;
use App\Modules\Core\Entitlement\Models\Entitlement;
use Carbon\CarbonImmutable;
class PublishedInvitation {
    public function find(string $slug): Invitation {
        $invitation = Invitation::with('publishedOrder')->where('slug', $slug)->firstOrFail();
        $order = $invitation->publishedOrder;
        abort_unless($invitation->published_at && $invitation->published_content && $order, 404);
        abort_unless($order->tenant_id === $invitation->tenant_id
            && $order->lifecycle()->isWithinPublicLifetimeAt(CarbonImmutable::now()), 404);
        abort_unless(Entitlement::where('tenant_id', $invitation->tenant_id)->where('product_id', $order->product_id)
            ->where('status', 'active')->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->exists(), 404);
        return $invitation;
    }
    public static function mediaIds(array $content): array {
        return array_values(array_filter(array_merge(
            [$content['cover_id'] ?? null, $content['partner_one_photo_id'] ?? null,
             $content['partner_two_photo_id'] ?? null, $content['music_id'] ?? null],
            $content['gallery_ids'] ?? []
        )));
    }
}
