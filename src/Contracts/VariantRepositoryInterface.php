<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Contracts;

use MyTree\NameProcessing\Domain\VariantDataset;

interface VariantRepositoryInterface
{
    public function load(string $datasetId): VariantDataset;
}
