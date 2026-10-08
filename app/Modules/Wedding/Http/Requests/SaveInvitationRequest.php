<?php
namespace App\Modules\Wedding\Http\Requests;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class SaveInvitationRequest extends FormRequest {
    public function authorize(): bool {
        app(ManualWeddingBilling::class)->editorOrder($this->user(), $this->route('tenant'));
        return true;
    }
    public function rules(): array {
        $image = fn () => Rule::exists('core_media', 'id')->where('tenant_id', $this->route('tenant')->id)->where('collection', 'wedding')->where('kind', 'image');
        return [
            'partner_one' => ['required', 'string', 'max:100'],
            'partner_two' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date_format:Y-m-d'],
            'venue' => ['required', 'string', 'max:300'],
            'message' => ['nullable', 'string', 'max:2000'],
            'template' => ['sometimes', 'required', Rule::in(array_keys(config('wedding_editor.templates')))],
            'partner_one_parents' => ['nullable', 'string', 'max:200'],
            'partner_two_parents' => ['nullable', 'string', 'max:200'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['sometimes', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
            'map_url' => ['nullable', 'url:http,https', 'max:1000'],
            'story' => ['nullable', 'string', 'max:5000'],
            'reception_date' => ['nullable', 'date_format:Y-m-d'],
            'reception_time' => ['nullable', 'date_format:H:i'],
            'reception_venue' => ['nullable', 'string', 'max:300', 'required_with:reception_date'],
            'cover_id' => ['nullable', 'integer', $image()],
            'partner_one_photo_id' => ['nullable', 'integer', $image()],
            'partner_two_photo_id' => ['nullable', 'integer', $image()],
            'gallery_ids' => ['sometimes', 'array', 'max:'.config('wedding_editor.gallery_items')],
            'gallery_ids.*' => ['integer', 'distinct', $image()],
            'music_id' => ['nullable', 'integer', Rule::exists('core_media', 'id')->where('tenant_id', $this->route('tenant')->id)->where('collection', 'wedding')->where('kind', 'music')],
            'rsvp_enabled' => ['sometimes', 'boolean'],
            'wishes_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
