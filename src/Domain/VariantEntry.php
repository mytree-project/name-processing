<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

final readonly class VariantEntry
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $value,
        public ?string $language,
        public ?string $script,
        public NameType $type,
        public array $metadata = [],
    ) {
    }
}
