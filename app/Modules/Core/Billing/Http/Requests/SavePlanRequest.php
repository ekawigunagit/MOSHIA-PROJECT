<?php

namespace App\Modules\Core\Billing\Http\Requests;

use App\Modules\Core\Billing\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('plan')
            ? $this->user()->can('update', $this->route('plan'))
            : $this->user()->can('create', Plan::class);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:core_products,id'],
            'name' => ['required', 'string', 'max:100', Rule::unique('core_plans', 'name')
                ->where('product_id', $this->input('product_id'))->ignore($this->route('plan')?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'commercial_terms' => ['prohibited'],
            'wedding_package' => [
                'nullable',
                'string',
                Rule::in(array_keys(config('wedding_plans.packages', []))),
                function (string $attribute, mixed $value, \Closure $fail) {
                    $productId = $this->input('product_id');
                    if (! is_scalar($productId) || ! \App\Modules\Core\Catalog\Models\Product::whereKey($productId)->where('slug', 'wedding')->exists()) {
                        $fail('Aturan paket Wedding hanya dapat digunakan untuk produk Wedding.');
                    }
                },
            ],
            'status' => ['prohibited'],
            'price' => ['prohibited'],
            'price_amount' => ['prohibited'],
            'trial_days' => ['prohibited'],
            'quota' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama paket sudah digunakan untuk produk ini.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'status.prohibited' => 'Paket hanya dapat disimpan sebagai draft pada tahap ini.',
        ];
    }
}
