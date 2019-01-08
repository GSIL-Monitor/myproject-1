<?php

namespace Common\Utils\Tenpay\Pub;

class SDKRuntimeException extends \Exception
{

    public function errorMessage()
    {
        return $this->getMessage();
    }
}

?>