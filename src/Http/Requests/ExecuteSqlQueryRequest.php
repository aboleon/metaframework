<?php

declare(strict_types=1);

namespace MetaFramework\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExecuteSqlQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && method_exists($user, 'hasRole')
            && $user->hasRole('dev|super-admin');
    }

    public function rules(): array
    {
        return [
            'sql_query' => ['required', 'string', 'max:10000'],
            'sql_query_affects_index' => ['nullable', 'boolean'],
            'return_path' => ['required', 'string', 'max:2048', 'regex:/^\/(?!\/)/'],
        ];
    }
}
