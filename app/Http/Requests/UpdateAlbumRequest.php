<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlbumRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'release_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'release_status' => ['nullable', 'in:draft,pending_review,published,rejected'],
            'scheduled_at' => ['nullable', 'date'],
            'cover_key' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
