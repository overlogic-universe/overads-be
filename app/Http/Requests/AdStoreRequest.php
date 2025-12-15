<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'=>'required|string|max:255',
            'type'=>'required|in:single,carousel,video',
            'description'=>'nullable|string',
            'theme'=>'nullable|string|max:100',
            'platforms'=>'required|array|min:1',
            'platforms.*'=>'in:instagram,facebook,instagram,linkedin',
            'reference_media'=>'nullable|file|mimes:jpeg,png,jpg,webp,mp4|max:51200'
        ];
    }
}
