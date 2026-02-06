<?php

declare(strict_types=1);

namespace MetaFramework\Services\Passwords;

use Illuminate\Support\Str;

/**
 * This class is responsible for generating and hashing passwords.
 */
final class PasswordGenerator
{
    private string $hashed_password; // The hashed version of the password.

    private string $public_password; // The unhashed (public) version of the password.

    private int $password_length = 8; // The default length of the password.

    /**
     * Set the public and hashed passwords.
     *
     * @param  string  $password  The public (unhashed) version of the password.
     */
    public function makePassword(string $password): void
    {
        // Set the public password.
        $this->public_password = $password;
        // Hash the password.
        $this->hashPassword();
    }

    /**
     * Hash the public password.
     */
    public function hashPassword(): void
    {
        // Hash the password using the bcrypt algorithm.
        $this->hashed_password = bcrypt($this->public_password);
    }

    /**
     * Get the public and hashed versions of the password.
     *
     * @return array<string> An array containing the encrypted and public versions of the password.
     */
    public function getPasswords(): array
    {
        return [
            'encrypted' => $this->hashed_password,
            'public' => $this->public_password,
        ];
    }

    /**
     * Generate a random public password and update the public password property.
     */
    public function generateRandomPublicPassword(): PasswordGenerator
    {
        $this->public_password = Str::random($this->password_length);

        return $this;
    }

    /**
     * Get the public version of the password.
     */
    public function getPublicPassword(): string
    {
        return $this->public_password;
    }

    /**
     * Get the encrypted version of the password.
     */
    public function getEncryptedPassword(): string
    {
        return $this->hashed_password;
    }
}
