<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

use JsonSerializable;
use LogicException;

final readonly class ProcessingResult implements JsonSerializable
{
    public const SCHEMA = 'mytree.name-processing.v1';

    /**
     * @param list<OutputValue> $results
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $operation,
        public NameInput $input,
        public Profile $profile,
        public array $results,
        public array $metadata = [],
    ) {
    }

    public function firstValue(): string
    {
        if ($this->results === []) {
            throw new LogicException('Processing result contains no values.');
        }

        return $this->results[0]->value;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'schema' => self::SCHEMA,
            'operation' => $this->operation,
            'input' => $this->input,
            'profile' => $this->profile,
            'results' => $this->results,
            'metadata' => $this->metadata,
        ];
    }
}
