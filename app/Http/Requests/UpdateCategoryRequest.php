<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Gate::allows('manageCategory', $this->category)) {
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
            'is_available' => "sometimes|required|boolean|max:1",
            'name_translation' => "sometimes|required|array|min:1",
            'name_translation.*' => "distinct|required",
            'description_translation' => "sometimes|required|array|min:1",
            'description_translation.*' => "distinct|required",
            'image_url' => 'nullable|sometimes|image' // |image|max:1024|mimes:png,jpg,gif,svg,jpeg
        ];
    }

    public function messages()
    {
        return [
            "name_translation.en" => "Name field is required",
            'image_url' => 'invalid image type'
        ];
    }
}
