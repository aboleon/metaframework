<?php

namespace App\Traits;

use Illuminate\Foundation\Http\FormRequest;
use MetaFramework\Traits\Responses;

trait ValidationAddress
{
    use Responses;

    /**
     * @var array<string, mixed>
     */
    protected array $validated_data = [];


    /**
     * Ensure we have correctly parsed validated data.
     */
    private function ensureDataIsValid(FormRequest $request): bool
    {

        $this->validated_data = is_array($request->validated()) && array_key_exists('wa_geo',$request->validated())
            ? (array)$request->validated('wa_geo')
            : [];

        if (!$this->validated_data) {
            $this->responseWarning("Les données n'ont pas pu être composées correctement.");
        }

        $this->validated_data['billing'] = isset($this->validated_data['billing']) ? 1 : null;

        return (bool)$this->validated_data;

    }
}
