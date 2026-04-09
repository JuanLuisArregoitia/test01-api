<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number' => [
                'sometimes', 'required', 'string', 'max:45',
                Rule::unique('orders', 'order_number')->ignore($this->order),
            ],
            'status_id' => ['sometimes', 'required', 'integer', 'between:1,3'],
            'client_id' => ['sometimes', 'required', 'integer', 'exists:clients,id'],
        ];
    }
}
