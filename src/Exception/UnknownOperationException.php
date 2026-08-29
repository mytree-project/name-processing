<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Exception;

final class UnknownOperationException extends NameProcessingException
{
    public function __construct(string $operation)
    {
        parent::__construct("Unknown operation: {$operation}", 'unknown_operation');
    }
}
