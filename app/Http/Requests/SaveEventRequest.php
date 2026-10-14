<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-cms') ?? false;
    }

    public function rules(): array
    {
        $photoIds = collect($this->route('event')?->gallery ?? [])->pluck('id')->all();

        return [
            'name' => ['required', 'string', 'max:200'],
            'sport' => ['required', Rule::in(['Running', 'Cycling', 'Triathlon', 'Multisport', 'Lainnya'])],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'event_date' => ['bail', 'nullable', 'string', 'date_format:Y-m-d', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_scalar($this->input('year')) || substr($value, 0, 4) !== (string) $this->input('year')) {
                    $fail('Tahun dan tanggal event harus sama.');
                }
            }],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'categories_text' => ['nullable', 'string', 'max:500'],
            'result_url' => ['nullable', 'url:http,https', 'max:2048'],
            'legacy_article_url' => ['nullable', 'url:http,https', 'max:2048'],
            'article_title' => ['nullable', 'required_if:article_published,1', 'string', 'max:255'],
            'article_body' => ['nullable', 'required_if:article_published,1', 'string', 'max:30000'],
            'published' => ['required', 'boolean'], 'featured' => ['required', 'boolean'],
            'article_published' => ['required', 'boolean'],
            'image_focus' => ['required', 'integer', 'between:0,100'],
            'revision' => [$this->route('event') ? 'required' : 'nullable', 'integer', 'min:1'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=8000'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => [Rule::in($photoIds)],
            'cover_id' => ['nullable', Rule::in($photoIds)],
            'captions' => ['nullable', 'array:'.implode(',', $photoIds)],
            'captions.*' => ['nullable', 'string', 'max:500'],
            'alt_texts' => ['nullable', 'array:'.implode(',', $photoIds)],
            'alt_texts.*' => ['required', 'string', 'max:255'],
            'action' => ['required', Rule::in(['save', 'generate'])],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama event', 'event_date' => 'tanggal event', 'year' => 'tahun',
            'location' => 'lokasi', 'description' => 'ringkasan', 'result_url' => 'tautan hasil',
            'photos.*' => 'foto', 'article_title' => 'judul artikel', 'article_body' => 'isi artikel'];
    }

    public function messages(): array
    {
        return ['required' => ':attribute wajib diisi.', 'max.string' => ':attribute maksimal :max karakter.',
            'photos.*.max' => 'Setiap foto maksimal 8 MB.', 'photos.*.image' => 'Gunakan foto JPG, PNG, atau WebP.',
            'url' => ':attribute harus berupa URL lengkap dengan https:// atau http://.',
            'required_if' => ':attribute wajib diisi sebelum artikel diterbitkan.'];
    }
}
