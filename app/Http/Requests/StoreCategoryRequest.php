<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'is_available' => "required|boolean|max:1",
            'name_translation' => "required|array|min:1",
            'name_translation.*' => "required|distinct|min:1",
            'description_translation' => "required|array|min:1",
            'description_translation.*' => "distinct",
            'image_url' => 'sometimes|nullable|image|max:1024|mimes:png,jpg,gif,svg,jpeg'
        ];
    }

    public function messages()
    {
        return [
            'name_translation.en' => 'Name is required'
        ];
    }
}
