<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Contracts;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

interface NameOperationInterface
{
    public function name(): string;

    public function defaultProfile(): string;

    public function process(NameInput $input, ?string $profileId = null): ProcessingResult;
}
