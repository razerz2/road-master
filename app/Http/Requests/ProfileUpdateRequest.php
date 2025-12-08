<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Se apenas avatar está sendo atualizado, name e email são opcionais
        $isAvatarOnly = $this->has('avatar') || $this->has('avatar_base64') || $this->has('remove_avatar');
        
        return [
            'name' => [$isAvatarOnly ? 'nullable' : 'required', 'string', 'max:255'],
            'email' => [
                $isAvatarOnly ? 'nullable' : 'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'avatar_base64' => ['nullable', 'string'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }
}
