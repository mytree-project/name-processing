<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

use JsonSerializable;

final readonly class Profile implements JsonSerializable
{
    /** @param array<string, mixed> $config */
    public function __construct(
        public string $id,
        public string $version,
        public string $operation,
        public array $config,
    ) {
    }

    /** @return array{id: string, version: string} */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
        ];
    }
}
