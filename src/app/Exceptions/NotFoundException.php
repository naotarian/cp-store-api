<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public function __construct(string $message = 'リソースが見つかりません', int $code = 404)
    {
        parent::__construct($message, $code);
    }
} 