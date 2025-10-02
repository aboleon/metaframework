<?php

namespace MetaFramework\Actions;


use MetaFramework\Support\Traits\Responses;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class Suppressor
{
    use Responses;

    protected Model $object;

    /**
     * @throws \Exception
     */
    public function __construct(Model $object)
    {
        $this->object = $object;
    }

    public function remove(): static
    {
        try {
            $this->object->delete();
        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            $this->responseElement('object', $this->object);
            return $this;
        }
    }
}
