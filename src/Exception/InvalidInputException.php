<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Exception;

final class InvalidInputException extends NameProcessingException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 'invalid_input');
    }
}
