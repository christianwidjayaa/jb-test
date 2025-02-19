<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')->ignore($this->route('post')),
            ],
            'content' => ['required', 'string'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Max 2MB
            'excerpt' => ['nullable', 'string', 'max:500'],
            'status' => [ Rule::in(['draft', 'published', 'archived'])],
            'is_featured' => ['boolean'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title is required.',
            'title.max' => 'The title must not exceed 255 characters.',
            'slug.required' => 'The slug is required.',
            'slug.unique' => 'The slug must be unique.',
            'content.required' => 'The content field is required.',
            'excerpt.max' => 'The excerpt must not exceed 500 characters.',
            'status.in' => 'Invalid status. Allowed values: draft, published, archived.',
            'is_featured.boolean' => 'Featured must be true or false.',
        ];
    }
}
