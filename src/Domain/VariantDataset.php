<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

final readonly class VariantDataset
{
    /**
     * @param list<VariantGroup> $groups
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $id,
        public string $version,
        public array $groups,
        public array $metadata = [],
    ) {
    }
}
