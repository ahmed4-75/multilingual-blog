<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id =  $this->route('role')->id;
        return [
            'name' => 'required|string|max:50|unique:roles,name,'.$id,
            // 'name' => ['required','string','max:50',Rule::unique('roles', 'name')->ignore($this->route('role')->id)]
            'permissions' => 'required|array',
            'permissions.*' => 'required|exists:permissions,id'
        ];
    }
}
