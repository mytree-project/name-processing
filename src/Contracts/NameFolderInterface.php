<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Contracts;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;

interface NameFolderInterface
{
    public function fold(NameInput $input, string $profileId = 'latin-search'): ProcessingResult;
}
