<?php

namespace Notifica\Exceptions;

use RuntimeException;
use Throwable;

class NotificaException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly array $response = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
