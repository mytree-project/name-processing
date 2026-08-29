<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

use JsonSerializable;

final readonly class OutputValue implements JsonSerializable
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $value,
        public ?string $language = null,
        public ?string $script = null,
        public ?NameType $type = null,
        public ?string $relation = null,
        public array $metadata = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'language' => $this->language,
            'script' => $this->script,
            'type' => $this->type?->value,
            'relation' => $this->relation,
            'metadata' => $this->metadata,
        ];
    }
}
