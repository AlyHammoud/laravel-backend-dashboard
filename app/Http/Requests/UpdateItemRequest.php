<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Gate::allows('manageItem', $this->item)) {
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
            'price' => 'nullable|numeric|min:0',
            'is_available' => 'sometimes|required|boolean|max:1',
            'category_id' => 'sometimes|required|exists:categories,id',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'nullable|image',
            'deleted_images' => 'sometimes|nullable|array',
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
