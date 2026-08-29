<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Contracts;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

interface NameNormalizerInterface
{
    public function normalize(NameInput $input, string $profileId = 'default'): ProcessingResult;
}
