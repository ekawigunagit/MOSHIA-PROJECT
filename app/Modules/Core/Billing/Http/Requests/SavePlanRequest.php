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
