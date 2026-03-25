<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ChangeRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $me */
        $me = Auth::user();
        $user = $this->route('user');
        if ($user->trashed() 
            or ($user->hasPermission('vi_user') and !$me->hasPermission('change_viu_roles')) 
            or (!$user->hasPermission('vi_user') and !$me->hasPermission('change_user_roles')))
        { return false; }
        return true;
    }

    /**
     * the message user get if unauthorized.
    */
    protected function failedAuthorization()
    {
        abort(403, 'The change role is Prevented because the user account is deactivated, or you do not have permission to change this user role.');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'required|exists:roles,id'
        ];
    }
}
