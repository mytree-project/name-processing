<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Application;

use MyTree\NameProcessing\Contracts\NameOperationInterface;
use MyTree\NameProcessing\Contracts\TransliteratorInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

final readonly class TransliterateOperation implements NameOperationInterface
{
    public function __construct(private TransliteratorInterface $service)
    {
    }

    public function name(): string
    {
        return 'transliterate';
    }

    public function defaultProfile(): string
    {
        return 'cyrillic-latin';
    }

    public function process(NameInput $input, ?string $profileId = null): ProcessingResult
    {
        return $this->service->transliterate($input, $profileId ?? $this->defaultProfile());
    }
}
