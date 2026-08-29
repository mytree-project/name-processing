<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Application;

use MyTree\NameProcessing\Contracts\NameFolderInterface;
use MyTree\NameProcessing\Contracts\NameOperationInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

final readonly class FoldOperation implements NameOperationInterface
{
    public function __construct(private NameFolderInterface $service)
    {
    }

    public function name(): string
    {
        return 'fold';
    }

    public function defaultProfile(): string
    {
        return 'latin-search';
    }

    public function process(NameInput $input, ?string $profileId = null): ProcessingResult
    {
        return $this->service->fold($input, $profileId ?? $this->defaultProfile());
    }
}
