<?php
namespace App\Modules\Products\Wedding\Services;
use App\Modules\Products\Wedding\Models\Invitation;
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
        $ids = [$content['cover_background_id'] ?? null];
        $fields = ['cover' => ['cover_id'], 'header' => ['header_image_id'], 'quote' => ['wedding_photo_id'],
            'groom' => ['partner_one_photo_id'], 'bride' => ['partner_two_photo_id'], 'thanks' => ['thank_you_image_id']];
        foreach ($fields as $section => $keys) {
            if (InvitationLayout::enabled($content, $section)) foreach ($keys as $key) $ids[] = $content[$key] ?? null;
        }
        if (InvitationLayout::enabled($content, 'header') && ($content['header_type'] ?? 'image') === 'video') $ids[] = $content['header_video_id'] ?? null;
        if (InvitationLayout::enabled($content, 'gallery')) $ids = array_merge($ids, $content['gallery_ids'] ?? []);
        if (InvitationLayout::enabled($content, 'story')) $ids = array_merge($ids, array_column($content['stories'] ?? [], 'image_id'));
        if ($content['music_enabled'] ?? true) $ids[] = $content['music_id'] ?? null;
        return array_values(array_unique(array_filter($ids)));
    }
}
