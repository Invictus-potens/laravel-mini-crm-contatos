<?php

namespace App\Infraestrutura\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('contacts', 'email')->ignore($this->route('id')),
            ],
            'phone' => ['required', 'string', 'max:20'],
        ];
    }
}