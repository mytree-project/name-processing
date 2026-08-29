<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

final readonly class VariantGroup
{
    /** @param list<VariantEntry> $variants */
    public function __construct(
        public string $id,
        public array $variants,
    ) {
    }
}
