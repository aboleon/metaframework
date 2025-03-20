<?php

namespace App\Actions\Ajax;

use Exception;
use MetaFramework\Traits\Responses;
use Throwable;

class AjaxAction
{
    use Responses;

    public function handle(callable $callback)
    {

        $this->enableAjaxMode();
        try {
            $callback($this);
        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->fetchResponse();
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    protected function checkRequestParams(array $params): array
    {
        $ret = [];
        foreach ($params as $param) {
            $v = request($param);
            if (!$v) {
                throw new Exception($param . ' is required');
            }
            $ret[] = $v;
        }
        return $ret;
    }
}
