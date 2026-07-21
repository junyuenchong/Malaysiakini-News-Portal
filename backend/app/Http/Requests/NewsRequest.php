<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * News API request validation.
 *
 * Used by:
 *   GET /api/news        → validates query params
 *   GET /api/news/{id}   → validates route id
 *
 * One class handles both routes. Rules change based on
 * whether the route contains an {id} parameter.
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
     * Copy route id into the request payload so it can be validated.
     */
    protected function prepareForValidation(): void
    {
        if ($this->route('id') !== null) {
            $this->merge(['id' => $this->route('id')]);
        }
    }

    /**
     * Validation rules depend on the route.
     *
     * Detail route: validate id
     * List route: validate query params
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

    /** Page number, minimum 1. */
    public function page(): int
    {
        return max(1, (int) $this->validated('page', 1));
    }

    /** Items per page, between 1 and 50. Default is 12. */
    public function perPage(): int
    {
        return min(50, max(1, (int) $this->validated('per_page', 12)));
    }

    /** News article id from the route. */
    public function newsId(): int
    {
        return (int) $this->validated('id');
    }
}
