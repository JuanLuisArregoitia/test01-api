<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['sometimes', 'required', 'integer', 'exists:orders,id'],
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
