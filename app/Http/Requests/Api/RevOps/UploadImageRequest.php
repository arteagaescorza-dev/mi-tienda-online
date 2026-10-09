<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'nullable|file|mimes:jpeg,jpg,png,webp,avif,bmp|max:15360',
            'image_url' => 'nullable|string|url|max:1000',
        ];
    }
}
