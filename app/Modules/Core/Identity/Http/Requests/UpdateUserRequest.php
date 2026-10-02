<?php

namespace App\Modules\Core\Identity\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->route('user')->id)],
            'roles' => $this->user()->is($this->route('user'))
                ? ['prohibited']
                : ['required', 'array', 'min:1', 'max:20'],
            'roles.*' => ['required', 'string', 'distinct', Rule::exists(Role::class, 'name')->where('guard_name', 'web')],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.prohibited' => 'Role akun sendiri tidak dapat diubah dari halaman ini.',
            'roles.required' => 'Pilih setidaknya satu role.',
            'roles.*.exists' => 'Role yang dipilih tidak tersedia untuk platform ini.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ];
    }
}
