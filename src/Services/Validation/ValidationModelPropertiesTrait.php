<?php

declare(strict_types=1);

namespace MetaFramework\Services\Validation;

use Exception;
use Illuminate\Support\Facades\Log;
use MetaFramework\Support\Traits\Responses;

trait ValidationModelPropertiesTrait
{
    use Responses;

    protected ?string $message = null;

    public function setInvalidPropertyMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getInvalidPropertyMessage(): string
    {
        if (is_null($this->message)) {
            $this->message = __('mfw::mfw.errors.error');
        }

        return $this->message;
    }

    /**
     * @throws Exception
     */
    public function validateModelProperty(
        string $property,
        string $message = '',
    ) {
        if ($message) {
            $this->setInvalidPropertyMessage($message);
        }

        $message = $this->getInvalidPropertyMessage();

        if ($this->{$property} === null) {
            if ($this->shouldBeException()) {
                throw new Exception($message);
            } else {
                $this->responseError($message);
                Log::error($message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));

                return $this;
            }
        }
    }
}
