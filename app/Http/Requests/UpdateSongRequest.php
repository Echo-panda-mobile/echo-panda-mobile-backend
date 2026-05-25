<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSongRequest extends FormRequest
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
            'album_id' => ['required', 'exists:albums,id'],
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'duration' => ['required', 'integer', 'min:1'],
            'track_number' => ['required', 'integer', 'min:1'],
            'lyrics' => ['nullable', 'string'],
            'original_key' => ['nullable', 'string', 'max:1024'],
            'cover_key' => ['nullable', 'string', 'max:1024'],
            'preview_key' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
