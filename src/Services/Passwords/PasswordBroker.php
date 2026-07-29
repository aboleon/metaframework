<?php

declare(strict_types=1);

namespace MetaFramework\Services\Passwords;

use Illuminate\Http\Request;
use MetaFramework\Services\Validation\ValidationTrait;

/**
 * This class is responsible for managing password change requests.
 */
final class PasswordBroker
{
    use ValidationTrait; // The Validation trait provides methods for validating input data.

    private PasswordRequest $requested; // The PasswordRequest object containing the password change data.

    private PasswordGenerator $generator; // The PasswordGenerator object used to generate new passwords.

    /**
     * Create a new PasswordBroker instance.
     *
     * @param  Request  $request  The HTTP request object containing the password change data.
     */
    public function __construct(Request $request)
    {
        // Create a new PasswordRequest object.
        $this->requested = new PasswordRequest($request);
        // Create a new PasswordGenerator object.
        $this->generator = new PasswordGenerator;

        $this->passwordBroker();
    }

    /**
     * Is there a request for password change ?
     */
    public function requestedChange(): bool
    {
        return $this->requested->requestedChange();
    }

    /**
     * Get the public and hashed versions of the password.
     *
     * @return array<string> An array containing the encrypted and public versions of the password.
     */
    public function getPasswords(): array
    {
        return $this->generator->getPasswords();
    }

    /**
     * Generate a new final password and update the password generator.
     *
     * @return PasswordBroker The current PasswordBroker instance.
     */
    public function passwordBroker(): PasswordBroker
    {
        $this->generator->makePassword($this->requested->password());

        return $this;
    }

    /**
     * The message showing the public password
     */
    public function printPublicPassword(): string
    {
        return __('mfw::mfw.passwords.is', ['password' => $this->generator->getPublicPassword()]);
    }

    /**
     * Gets the encrypted version of the generated password
     */
    public function getEncryptedPassword(): string
    {
        return $this->generator->getEncryptedPassword();
    }

    public function getPublicPassword(): string
    {
        return $this->generator->getPublicPassword();
    }
}
