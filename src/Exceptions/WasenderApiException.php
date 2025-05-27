<?php

namespace WasenderApi\Exceptions;

use Exception;

class WasenderApiException extends Exception
{
    protected $response;
    public function __construct($message = '', $code = 0, $response = null)
    {
        parent::__construct($message, $code);
        $this->response = $response;
    }
    public function getResponse()
    {
        return $this->response;
    }
} 