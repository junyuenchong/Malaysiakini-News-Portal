<?php

namespace App\Modules\News\Http\Requests;

use App\Support\Http\Concerns\MergesRouteId;
use App\Support\Http\Concerns\PaginatesRequests;
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
    use MergesRouteId;
    use PaginatesRequests;

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
}
