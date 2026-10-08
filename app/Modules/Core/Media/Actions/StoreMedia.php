<?php
namespace App\Modules\Core\Media\Actions;
use App\Models\User;
use App\Modules\Core\Media\Models\Media;
use App\Modules\Core\Tenancy\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class StoreMedia {
    public function handle(User $user, Tenant $tenant, UploadedFile $file, string $kind, string $collection): Media {
        Validator::make(compact('kind', 'collection'), [
            'kind' => ['required', Rule::in(['image','music'])],
            'collection' => ['required', Rule::in(config('core_media.collections'))],
        ])->validate();
        Validator::make(['file' => $file], ['file' => $kind === 'image'
            ? ['required','file','image','mimes:jpg,jpeg,png,webp','max:'.config('core_media.image_kb'),'dimensions:max_width=8000,max_height=8000']
            : ['required','file','mimes:mp3','max:'.config('core_media.music_kb')]])->validate();
        $disk = config('core_media.disk');
        $path = null;
        try {
            return DB::transaction(function () use ($user, $tenant, $file, $kind, $collection, $disk, &$path) {
                $locked = Tenant::lockForUpdate()->findOrFail($tenant->id);
                abort_unless($user->hasVerifiedEmail() && $locked->owner_id === $user->id
                    && $locked->members()->whereKey($user->id)->exists(), 403);
                if (Media::where('tenant_id', $tenant->id)->where('collection', $collection)->count()
                    >= config('core_media.items_per_workspace_collection')) {
                    throw ValidationException::withMessages(['file' => 'Batas teknis media tercapai. Hubungi pengelola untuk membersihkan file yang tidak dipakai.']);
                }
                $path = $file->store('media/'.$tenant->id.'/'.$collection, $disk);
                if (! $path) { throw ValidationException::withMessages(['file' => 'Penyimpanan file gagal. Coba kembali.']); }
                return Media::create(['tenant_id' => $tenant->id, 'collection' => $collection, 'kind' => $kind,
                    'path' => $path, 'mime' => $file->getMimeType(), 'size' => $file->getSize()]);
            });
        } catch (\Throwable $e) {
            if ($path) { Storage::disk($disk)->delete($path); }
            throw $e;
        }
    }
}
