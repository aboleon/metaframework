<?php

namespace App\Traits;

trait InvalidInput
{
    public function hasInvalidFillable(): bool
    {
        $is_invalid = false;
        foreach ($this->fillable as $value) {
            if (empty($this->{$value})) {
                $is_invalid = true;
            }
        }
        return $is_invalid;
    }

    public function invalidInputField($value): string
    {
        return empty($value) ? 'is-invalid' : '';
    }
}
