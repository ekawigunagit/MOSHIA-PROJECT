<?php
namespace App\Modules\Wedding\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Tenancy\Models\Tenant;
use App\Modules\Core\Media\Actions\StoreMedia;
use App\Modules\Core\Media\Models\Media;
use App\Modules\Core\Media\Services\MediaFiles;
use App\Modules\Wedding\Services\PublishedInvitation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class MediaController extends Controller {
    public function store(Request $request, Tenant $tenant, ManualWeddingBilling $billing, StoreMedia $store) {
        $billing->editorOrder($request->user(), $tenant);
        $request->validate(['kind' => ['required', Rule::in(['image','music'])], 'file' => ['required','file']]);
        $store->handle($request->user(), $tenant, $request->file('file'), $request->input('kind'), 'wedding');
        return back()->with('success', 'Media diunggah secara privat. Pilih media dan simpan draft sebelum Publish.');
    }
    public function privateFile(Request $request, Tenant $tenant, Media $media, ManualWeddingBilling $billing, MediaFiles $files) {
        $billing->editorOrder($request->user(), $tenant);
        abort_unless($media->tenant_id === $tenant->id && $media->collection === 'wedding', 404);
        return $files->response($media);
    }
    public function publicFile(string $slug, Media $media, PublishedInvitation $published, MediaFiles $files) {
        $invitation = $published->find($slug);
        abort_unless($media->tenant_id === $invitation->tenant_id && $media->collection === 'wedding'
            && in_array($media->id, PublishedInvitation::mediaIds($invitation->published_content)), 404);
        return $files->response($media);
    }
}
