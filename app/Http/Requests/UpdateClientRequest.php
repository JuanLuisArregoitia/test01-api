<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:45'],
            'lastname' => ['sometimes', 'required', 'string', 'max:45'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:100',
                Rule::unique('clients', 'email')->ignore($this->client),
            ],
        ];
    }
}
