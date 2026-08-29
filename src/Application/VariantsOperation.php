<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Application;

use MyTree\NameProcessing\Contracts\NameOperationInterface;
use MyTree\NameProcessing\Contracts\NameVariantResolverInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

final readonly class VariantsOperation implements NameOperationInterface
{
    public function __construct(private NameVariantResolverInterface $service)
    {
    }

    public function name(): string
    {
        return 'variants';
    }

    public function defaultProfile(): string
    {
        return 'genealogy-pl-ru';
    }

    public function process(NameInput $input, ?string $profileId = null): ProcessingResult
    {
        return $this->service->resolve($input, $profileId ?? $this->defaultProfile());
    }
}
