<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Exception;

final class UnknownProfileException extends NameProcessingException
{
    public function __construct(string $operation, string $profileId)
    {
        parent::__construct(
            "Unknown profile '{$profileId}' for operation '{$operation}'.",
            'unknown_profile',
        );
    }
}
