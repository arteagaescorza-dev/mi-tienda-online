<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate is handled in the controller
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'universe' => 'required|string|in:all,marte,venus,tiresias,terra',
            'status' => 'nullable|string|in:active,draft,archived',
            'type' => 'required|string',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'badge' => 'nullable|string|max:255',
            'custom_html' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'settings' => 'nullable|array',
            'background_image_file' => 'nullable|image|max:5120',
            'background_image_url' => 'nullable|string',
        ];
    }
}
