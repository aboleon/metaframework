<?php

declare(strict_types=1);

namespace MetaFramework\Services\Passwords;

use Illuminate\Http\Request;

final class PasswordValidationSet
{
    private PasswordRequest $password_request;

    public function __construct(Request $request)
    {
        $this->password_request = new PasswordRequest($request);
    }

    /**
     * @return array<array<string>>
     */
    public function logic(): array
    {
        if (
            $this->password_request->randomPasswordRequested()
        ) {
            return [
                'rules' => [],
                'messages' => []
            ];
        }

        return (new PasswordValidation)->logic();
    }
}
