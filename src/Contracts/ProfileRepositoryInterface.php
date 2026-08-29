<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Contracts;

use MyTree\NameProcessing\Domain\Profile;

interface ProfileRepositoryInterface
{
    public function get(string $operation, string $profileId): Profile;
}
