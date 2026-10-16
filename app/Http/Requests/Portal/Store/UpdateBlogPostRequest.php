<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-cms');
    }

    public function rules(): array
    {
        $postParam = $this->route('post');
        $postId = is_object($postParam) ? $postParam->id : $postParam;

        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_blog_posts,slug,' . $postId,
            'category_name' => 'required|string|max:100',
            'tags' => 'nullable',
            'author_name' => 'nullable|string|max:100',
            'reading_time_minutes' => 'nullable|integer|min:1',
            'excerpt' => 'required|string',
            'body' => 'required|string',
            'status' => 'nullable|in:published,draft',
        ];
    }
}
