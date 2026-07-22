<?php

namespace App\Modules\News\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * News API request validation.
 *
 * Used by:
 *   GET /api/news        → validates query params
 *   GET /api/news/{id}   → validates route id
 */
class NewsRequest extends FormRequest
{
    /**
     * All news endpoints are public read routes.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules depend on the route.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->route('id') !== null) {
            return [
                'id' => ['required', 'integer', 'min:1'],
            ];
        }

        return [
            'category' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * Category slug filter.
     * Returns null when no category param is sent.
     */
    public function categorySlug(): ?string
    {
        $value = $this->validated('category');

        return $value !== null && $value !== '' ? $value : null;
    }

    /** News article id from the route. */
    public function newsId(): int
    {
        return (int) $this->validated('id');
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('id') !== null) {
            $this->merge(['id' => $this->route('id')]);
        }
    }

    public function page(): int
    {
        return max(1, (int) $this->validated('page', 1));
    }

    public function perPage(): int
    {
        return min(50, max(1, (int) $this->validated('per_page', 12)));
    }
}
