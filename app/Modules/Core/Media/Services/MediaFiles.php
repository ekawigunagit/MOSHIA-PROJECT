<?php
namespace App\Modules\Core\Media\Services;
use App\Modules\Core\Media\Models\Media;
use Illuminate\Support\Facades\Storage;
class MediaFiles {
    // Caller must authorize product visibility before requesting a response.
    public function response(Media $media) {
        $disk = Storage::disk(config('core_media.disk'));
        abort_unless($disk->exists($media->path), 404);
        return response()->file($disk->path($media->path), [
            'Content-Type' => $media->mime, 'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff', 'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
