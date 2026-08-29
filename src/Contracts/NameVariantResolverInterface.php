<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Contracts;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

interface NameVariantResolverInterface
{
    public function resolve(NameInput $input, string $profileId = 'genealogy-pl-ru'): ProcessingResult;
}
