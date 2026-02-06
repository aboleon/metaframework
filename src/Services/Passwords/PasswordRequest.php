<?php

declare(strict_types=1);

namespace MetaFramework\Services\Passwords;

use Illuminate\Http\Request;

/**
 * This class represents a request to change a password.
 */
final class PasswordRequest
{
    private bool $requestedChange;

    private string $password;

    private bool $randomPasswordRequested;

    /**
     * Create a new PasswordRequest instance.
     *
     * @param  Request  $request  The HTTP request object containing the password change data.
     */
    public function __construct(Request $request)
    {
        $this->requestedChange = $request->has('password_change');
        $this->password = strval($request->input('password'));
        $this->randomPasswordRequested = $request->has('random_password');
    }

    /**
     * Determine whether a password change was requested.
     *
     * @return bool True if a password change was requested, false otherwise.
     */
    public function requestedChange(): bool
    {
        return $this->requestedChange;
    }

    /**
     * Determine whether a random password was requested.
     *
     * @return bool True if a random password was requested, false otherwise.
     */
    public function randomPasswordRequested(): bool
    {
        return $this->randomPasswordRequested or ($this->requestedChange && $this->password === '');
    }

    /**
     * Get the password entered by the user.
     *
     * @return string The password entered by the user.
     */
    public function password(): string
    {
        $this->validate();

        return $this->password;
    }

    /**
     * Validate the password change request
     */
    public function validate(): void
    {
        if (!$this->password) {
            $this->password = (new PasswordGenerator)->generateRandomPublicPassword()->getPublicPassword();
        }
    }
}
