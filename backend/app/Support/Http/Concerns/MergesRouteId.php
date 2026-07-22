<?php

namespace App\Support\Http\Concerns;

/**
 * Copy route {id} into the request payload so it can be validated.
 */
trait MergesRouteId
{
    protected function prepareForValidation(): void
    {
        if ($this->route('id') !== null) {
            $this->merge(['id' => $this->route('id')]);
        }
    }
}
