<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Application;

use MyTree\NameProcessing\Contracts\NameNormalizerInterface;
use MyTree\NameProcessing\Contracts\NameOperationInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

final readonly class NormalizeOperation implements NameOperationInterface
{
    public function __construct(private NameNormalizerInterface $service)
    {
    }

    public function name(): string
    {
        return 'normalize';
    }

    public function defaultProfile(): string
    {
        return 'default';
    }

    public function process(NameInput $input, ?string $profileId = null): ProcessingResult
    {
        return $this->service->normalize($input, $profileId ?? $this->defaultProfile());
    }
}
