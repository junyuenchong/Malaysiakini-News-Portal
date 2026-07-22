<?php

namespace App\Modules\Category\Http\Requests;

use App\Support\Http\Concerns\MergesRouteId;
use App\Support\Http\Concerns\PaginatesRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates GET /api/categories/{id}/news query params and route id.
 */
class CategoryNewsRequest extends FormRequest
{
    use MergesRouteId;
    use PaginatesRequests;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function categoryId(): int
    {
        return (int) $this->validated('id');
    }
}
