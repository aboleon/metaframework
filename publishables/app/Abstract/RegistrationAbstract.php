<?php

namespace App\Abstract;

use App\Models\UserRegistration;
use MetaFramework\Traits\Responses;

abstract class RegistrationAbstract {

    use Responses;

    protected ?UserRegistration $instance = null;

    public function setInstance(UserRegistration $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    protected function checkInstanceState(): void
    {
        // If the registration hasn't been validated yet

        if ($this->instance->validated_at && $this->instance->terminated_at) {
            // Default warning if already registered
            $this->responseWarning(__('front/register.you_have_already_registered'));
        }

        // If validated but not terminated
        if ($this->instance->validated_at && ! $this->instance->terminated_at) {
            $this->responseWarning(__('front/register.you_have_already_validated'));
        }
    }
}
