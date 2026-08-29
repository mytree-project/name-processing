<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Exception;

use RuntimeException;
use Throwable;

class NameProcessingException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $errorCode = 'processing_error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
