<?php

namespace App\Modules\Core\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'icon' => ['required', Rule::in(array_keys(config('moshia.catalog.icons')))],
            'status' => ['required', Rule::in(array_keys(config('moshia.catalog.statuses')))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'id' => ['prohibited'],
            'slug' => ['prohibited'],
            'phase' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Pilih status Segera hadir atau Disembunyikan.',
            'icon.in' => 'Pilih ikon yang tersedia.',
            'id.prohibited' => 'Identitas produk tidak dapat diubah.',
            'slug.prohibited' => 'Identitas produk tidak dapat diubah.',
            'phase.prohibited' => 'Fase roadmap tidak dapat diubah dari katalog.',
        ];
    }
}
