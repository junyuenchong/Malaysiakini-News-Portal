<?php

namespace App\Support\Http\Concerns;

/**
 * Shared pagination accessors for FormRequest classes.
 */
trait PaginatesRequests
{
    public function page(): int
    {
        return max(1, (int) $this->validated('page', 1));
    }

    public function perPage(): int
    {
        return min(50, max(1, (int) $this->validated('per_page', 12)));
    }
}
