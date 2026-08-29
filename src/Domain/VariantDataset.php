<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

final readonly class VariantDataset
{
    /** @param list<VariantGroup> $groups */
    public function __construct(
        public string $id,
        public string $version,
        public array $groups,
    ) {
    }
}
