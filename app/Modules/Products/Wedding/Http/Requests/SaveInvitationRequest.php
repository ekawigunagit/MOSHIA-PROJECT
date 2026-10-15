<?php
namespace App\Modules\Products\Wedding\Http\Requests;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class SaveInvitationRequest extends FormRequest {
    public function authorize(): bool {
        app(ManualWeddingBilling::class)->editorOrder($this->user(), $this->route('tenant'));
        return true;
    }
    protected function prepareForValidation(): void {
        $this->merge([
            'event_name' => $this->input('event_name', 'Pemberkatan Pernikahan'),
            'reception_enabled' => $this->input('reception_enabled', $this->filled('reception_date')),
            'reception_name' => $this->input('reception_name', 'Resepsi'),
        ]);
    }
    public function rules(): array {
        $image = fn () => Rule::exists('core_media', 'id')->where('tenant_id', $this->route('tenant')->id)->where('collection', 'wedding')->where('kind', 'image');
        $videoAllowed = \App\Modules\Products\Wedding\Services\WeddingFeatures::videoHeader(
            app(ManualWeddingBilling::class)->editorOrder($this->user(), $this->route('tenant')));
        return [
            'sections' => ['sometimes','array','size:'.count(\App\Modules\Products\Wedding\Services\InvitationLayout::LABELS)],
            'sections.*' => ['array:key,enabled'],
            'sections.*.key' => ['required','distinct', Rule::in(array_keys(\App\Modules\Products\Wedding\Services\InvitationLayout::LABELS))],
            'sections.*.enabled' => ['required','boolean'],
            'cover_background_id' => ['nullable','integer',$image()],
            'background_color' => ['nullable','regex:/^#[0-9a-fA-F]{6}$/'],
            'background_opacity' => ['sometimes','numeric','between:0.2,0.95'],
            'cover_title' => ['nullable','string','max:150'],
            'cover_greeting' => ['nullable','string','max:150'],
            'cover_opening' => ['nullable','string','max:2000'],
            'cover_button' => ['nullable','string','max:80'],
            'cover_closing' => ['nullable','string','max:300'],
            'music_enabled' => ['sometimes','boolean'],
            'header_type' => ['sometimes', Rule::in($videoAllowed ? ['image', 'video'] : ['image'])],
            'header_image_id' => ['nullable', 'integer', $image()],
            'header_video_id' => [Rule::prohibitedIf(! $videoAllowed), 'nullable', 'integer', Rule::exists('core_media', 'id')->where('tenant_id', $this->route('tenant')->id)->where('collection', 'wedding')->where('kind', 'video')],
            'wedding_photo_id' => ['nullable', 'integer', $image()],
            'wedding_quote' => ['nullable', 'string', 'max:3000'],
            'save_date_title' => ['nullable', 'string', 'max:150'],
            'save_date_message' => ['nullable', 'string', 'max:2000'],
            'stories' => ['sometimes', 'array', 'max:'.config('wedding_editor.story_items')],
            'stories.*' => ['array:title,body,image_id'],
            'stories.*.title' => ['required', 'string', 'max:150'],
            'stories.*.body' => ['nullable', 'string', 'max:3000'],
            'stories.*.image_id' => ['nullable', 'integer', $image()],
            'gift_enabled' => ['sometimes', 'boolean'],
            'gift_accounts' => [Rule::requiredIf($this->boolean('gift_enabled')), 'array', 'max:'.config('wedding_editor.gift_accounts')],
            'gift_accounts.*' => ['array:bank_id,account_number,account_holder,enabled'],
            'gift_accounts.*.enabled' => ['sometimes','boolean'],
            'gift_accounts.*.bank_id' => ['required', 'integer', 'exists:core_banks,id'],
            'gift_accounts.*.account_number' => ['required', 'string', 'regex:/^[0-9]{5,40}$/'],
            'gift_accounts.*.account_holder' => ['required', 'string', 'max:150'],
            'thank_you_image_id' => ['nullable', 'integer', $image()],
            'thank_you_title' => ['nullable', 'string', 'max:150'],
            'thank_you_message' => ['nullable', 'string', 'max:3000'],
            'partner_one' => ['required', 'string', 'max:100'],
            'partner_two' => ['required', 'string', 'max:100'],
            'event_name' => ['required', 'string', 'max:100'],
            'reception_enabled' => ['required', 'boolean'],
            'reception_name' => [Rule::requiredIf($this->boolean('reception_enabled')), 'nullable', 'string', 'max:100'],
            'reception_map_url' => ['nullable', 'url:http,https', 'max:1000'],
            'event_date' => ['required', 'date_format:Y-m-d'],
            'venue' => ['required', 'string', 'max:300'],
            'message' => ['nullable', 'string', 'max:2000'],
            'template' => ['sometimes', 'required', Rule::in(array_keys(config('wedding_editor.templates')))],
            'partner_order' => ['sometimes', 'required', Rule::in(['groom_first', 'bride_first'])],
            'partner_one_father' => ['nullable', 'string', 'max:200'],
            'partner_one_mother' => ['nullable', 'string', 'max:200'],
            'partner_two_father' => ['nullable', 'string', 'max:200'],
            'partner_two_mother' => ['nullable', 'string', 'max:200'],
            'partner_one_parents' => ['nullable', 'string', 'max:200'],
            'partner_two_parents' => ['nullable', 'string', 'max:200'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['sometimes', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
            'map_url' => ['nullable', 'url:http,https', 'max:1000'],
            'story' => ['nullable', 'string', 'max:5000'],
            'reception_date' => [Rule::requiredIf($this->boolean('reception_enabled')), 'nullable', 'date_format:Y-m-d'],
            'reception_time' => ['nullable', 'date_format:H:i'],
            'reception_venue' => ['nullable', 'string', 'max:300', Rule::requiredIf($this->boolean('reception_enabled'))],
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
