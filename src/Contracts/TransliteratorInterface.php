<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Contracts;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

interface TransliteratorInterface
{
    public function transliterate(NameInput $input, string $profileId = 'cyrillic-latin'): ProcessingResult;
}
