<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Gate::allows('manageProduct', $this->product)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name_translation' => "sometimes|required|array|min:1",
            'name_translation.*' => "distinct|required",
            'description_translation' => "sometimes|required|array|min:1",
            'description_translation.*' => "distinct",
            'price' => 'numeric|min:0.01',
            'is_available' => 'sometimes|required|boolean|max:1',
            'item_id' => 'sometimes|required|exists:items,id',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'image',
            'deleted_images' => 'sometimes|nullable|array',
            'deleted_images.*' => 'string',

            'quantity' => 'sometimes|required|numeric|min:0',
            'sale' => 'sometimes|numeric|max:100|min:0',
            'color' => 'sometimes|nullable|array',
            'size' => 'sometimes|nullable|array'

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
