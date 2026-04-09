<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:45', 'unique:orders,order_number'],
            'status_id' => ['required', 'integer', 'between:1,3'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
        ];
    }
}
