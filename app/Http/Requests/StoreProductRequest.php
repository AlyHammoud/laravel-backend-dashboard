<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name_translation' => "required|array|min:1",
            'name_translation.*' => "required|distinct",
            'description_translation' => "required|array|min:1",
            'description_translation.*' => "distinct",
            'price' => 'numeric|min:0.01',
            'is_available' => 'required|boolean|max:1',
            'item_id' => 'required|exists:items,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image',

            'quantity' => 'nullable|numeric|min:0',
            'sale' => 'nullable|numeric|max:100|min:0',
            'size' => 'nullable|array',
            'color' => 'nullable|array'
        ];
    }

    public function messages()
    {
        return [
            'name_translation.en' => 'Name is required',
            'images.0' => 'image field only accepts images'
        ];
    }
}
