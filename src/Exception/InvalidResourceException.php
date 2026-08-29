<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Exception;

use Throwable;

final class InvalidResourceException extends NameProcessingException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 'invalid_resource', $previous);
    }
}
