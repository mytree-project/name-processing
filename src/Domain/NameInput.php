<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

use JsonSerializable;
use MyTree\NameProcessing\Exception\InvalidInputException;

final readonly class NameInput implements JsonSerializable
{
    public function __construct(
        public string $value,
        public ?string $language = null,
        public ?string $script = null,
        public ?NameType $type = null,
    ) {
        if (!mb_check_encoding($this->value, 'UTF-8')) {
            throw new InvalidInputException('Name value must be valid UTF-8.');
        }

        if (preg_match('/\S/u', $this->value) !== 1) {
            throw new InvalidInputException('Name value must not be empty.');
        }
    }

    /** @return array{value: string, language: ?string, script: ?string, type: ?string} */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'language' => $this->language,
            'script' => $this->script,
            'type' => $this->type?->value,
        ];
    }
}
