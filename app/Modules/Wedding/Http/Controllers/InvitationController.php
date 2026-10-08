<?php

namespace App\Modules\Wedding\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Tenancy\Models\Tenant;
use App\Modules\Wedding\Http\Requests\SaveInvitationRequest;
use App\Modules\Wedding\Models\Invitation;
use App\Modules\Core\Media\Models\Media;
use App\Modules\Wedding\Models\GuestResponse;
use App\Modules\Wedding\Services\PublishedInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function edit(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $order = $billing->editorOrder($request->user(), $tenant);
        $invitation = Invitation::where('tenant_id', $tenant->id)->firstOrFail();
        $responses = GuestResponse::where('invitation_id', $invitation->id);

        return Inertia::render('Wedding/Edit', [
            'workspace' => $tenant->only('id', 'name'),
            'invitation' => $invitation->only('draft_content', 'published_at'),
            'order' => $order->only('id', 'terms', 'first_published_at', 'ends_at'),
            'publicUrl' => route('wedding.public', $invitation->slug),
            'templates' => collect(config('wedding_editor.templates'))->map(fn ($t, $key) => ['key' => $key, ...$t])->values(),
            'media' => Media::where('tenant_id', $tenant->id)->where('collection', 'wedding')->latest('id')->get()->map(fn ($m) => [
                'id' => $m->id, 'kind' => $m->kind, 'size' => $m->size,
                'url' => route('wedding.media.private', [$tenant->id, $m->id]),
            ]),
            'responses' => (clone $responses)->latest('id')->paginate(10, ['id','name','attendance','guests','wish','approved','created_at'], 'responses_page')->withQueryString(),
            'responseStats' => [
                'total' => (clone $responses)->count(),
                'attending' => (clone $responses)->where('attendance', 'yes')->sum('guests'),
                'pendingWishes' => (clone $responses)->whereNotNull('wish')->where('approved', false)->count(),
            ],
            'limits' => [
                'imageKb' => config('core_media.image_kb'), 'musicKb' => config('core_media.music_kb'),
                'gallery' => config('wedding_editor.gallery_items'), 'files' => config('core_media.items_per_workspace_collection'),
            ],
        ]);
    }

    public function update(SaveInvitationRequest $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->saveContent($request->user(), $tenant, $request->validated());
        return back()->with('success', 'Draft tersimpan. Preview sudah diperbarui; halaman publik berubah setelah Publish.');
    }

    public function publish(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->publish($request->user(), $tenant);
        return back()->with('success', 'Undangan berhasil dipublikasikan.');
    }

    public function unpublish(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->unpublish($request->user(), $tenant);

        return back()->with('success', 'Undangan ditutup dari publik. Data tersimpan dan masa aktif tetap berjalan.');
    }

    public function preview(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->editorOrder($request->user(), $tenant);
        $invitation = Invitation::where('tenant_id', $tenant->id)->firstOrFail();
        abort_unless($invitation->draft_content, 404);
        return $this->render($invitation, $invitation->draft_content, true);
    }

    public function show(string $slug, PublishedInvitation $published)
    {
        $invitation = $published->find($slug);
        return $this->render($invitation, $invitation->published_content, false);
    }

    private function render(Invitation $invitation, array $content, bool $preview)
    {
        $theme = config('wedding_editor.templates.'.($content['template'] ?? 'classic'))
            ?? config('wedding_editor.templates.classic');
        $mediaUrls = [];
        foreach (PublishedInvitation::mediaIds($content) as $id) {
            $mediaUrls[$id] = $preview
                ? route('wedding.media.private', [$invitation->tenant_id, $id])
                : route('wedding.media.public', [$invitation->slug, $id]);
        }
        $wishes = ($content['wishes_enabled'] ?? false)
            ? GuestResponse::where('invitation_id', $invitation->id)->where('approved', true)
                ->whereNotNull('wish')->latest('id')->limit(30)->get(['name','wish'])
            : collect();

        return response()->view('wedding.invitation', compact('content', 'preview', 'theme', 'mediaUrls', 'invitation', 'wishes'))
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
