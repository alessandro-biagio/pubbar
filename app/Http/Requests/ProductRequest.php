<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->is_staff ?? false; }

    public function rules(): array
    {
        return [
            'category_id'  => ['required','exists:categories,id'],
            'name'         => ['required','string','max:255'],
            'description'  => ['nullable','string','max:5000'],
            'price'        => ['required','decimal:0,2','min:0'],
            'is_available' => ['sometimes','boolean'],
            'image'        => [$this->isMethod('post') ? 'required' : 'nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ];
    }
}
