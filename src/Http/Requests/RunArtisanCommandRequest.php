<?php

declare(strict_types=1);

namespace MetaFramework\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use MetaFramework\Support\UserRoles;

class RunArtisanCommandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && ((method_exists($user, 'isDev') && $user->isDev())
                || (method_exists($user, 'hasRole') && $user->hasRole(UserRoles::CORE_DEV_KEY)));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'command' => ['required', 'string', 'max:1000', 'not_regex:/[\x00-\x1F\x7F]/u'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'command.not_regex' => __('mfw::mfw.dev.artisan.invalid_characters'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $command = Str::of((string) $this->input('command'))->trim();

        if ($command->test('/^php\s+artisan(?:\s+|$)/i')) {
            $command = $command->replaceMatches('/^php\s+artisan\s*/i', '');
        }

        $this->merge(['command' => (string) $command]);
    }
}
