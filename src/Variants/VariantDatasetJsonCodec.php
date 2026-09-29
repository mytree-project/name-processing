<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Variants;

use JsonException;
use MyTree\NameProcessing\Domain\NameType;
use MyTree\NameProcessing\Domain\VariantDataset;
use MyTree\NameProcessing\Domain\VariantEntry;
use MyTree\NameProcessing\Domain\VariantGroup;
use MyTree\NameProcessing\Exception\InvalidResourceException;

final class VariantDatasetJsonCodec
{
    public const SCHEMA = 'mytree.name-variants.v1';

    public function decode(string $json, ?string $expectedDatasetId = null): VariantDataset
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidResourceException('Invalid variant dataset JSON.', $e);
        }

        if (! is_array($data)) {
            throw new InvalidResourceException('Variant dataset must decode to an object.');
        }

        if (($data['schema'] ?? null) !== self::SCHEMA) {
            throw new InvalidResourceException('Unsupported variant dataset schema.');
        }

        $datasetId = $data['id'] ?? null;
        $version = $data['version'] ?? null;
        $groupsData = $data['groups'] ?? null;

        if (! is_string($datasetId) || $datasetId === '' || ! is_string($version) || $version === '' || ! is_array($groupsData)) {
            throw new InvalidResourceException('Invalid variant dataset metadata.');
        }

        if ($expectedDatasetId !== null && $datasetId !== $expectedDatasetId) {
            throw new InvalidResourceException("Variant dataset id mismatch: expected {$expectedDatasetId}, got {$datasetId}.");
        }

        $groups = [];
        foreach ($groupsData as $groupData) {
            if (! is_array($groupData) || ! is_string($groupData['id'] ?? null) || ! is_array($groupData['variants'] ?? null)) {
                throw new InvalidResourceException('Invalid variant group.');
            }

            $variants = [];
            foreach ($groupData['variants'] as $entryData) {
                if (! is_array($entryData) || ! is_string($entryData['value'] ?? null)) {
                    throw new InvalidResourceException('Invalid variant entry.');
                }

                $typeRaw = $entryData['type'] ?? null;
                $type = is_string($typeRaw) ? NameType::tryFrom($typeRaw) : null;
                if ($type === null) {
                    throw new InvalidResourceException('Invalid variant type.');
                }

                $metadata = $entryData['metadata'] ?? [];
                if (! is_array($metadata)) {
                    throw new InvalidResourceException('Variant metadata must be an object.');
                }

                $variants[] = new VariantEntry(
                    value: $entryData['value'],
                    language: is_string($entryData['language'] ?? null) ? $entryData['language'] : null,
                    script: is_string($entryData['script'] ?? null) ? $entryData['script'] : null,
                    type: $type,
                    metadata: $metadata,
                );
            }

            $groups[] = new VariantGroup($groupData['id'], $variants);
        }

        $metadata = $data;
        unset($metadata['schema'], $metadata['id'], $metadata['version'], $metadata['groups']);

        return new VariantDataset($datasetId, $version, $groups, $metadata);
    }

    public function encode(VariantDataset $dataset): string
    {
        $data = [
            'schema' => self::SCHEMA,
            'id' => $dataset->id,
            'version' => $dataset->version,
        ];

        foreach ($this->normalizeAssociativeArray($dataset->metadata) as $key => $value) {
            if (in_array($key, ['schema', 'id', 'version', 'groups'], true)) {
                continue;
            }

            $data[$key] = $value;
        }

        $data['groups'] = array_map(
            fn (VariantGroup $group): array => [
                'id' => $group->id,
                'variants' => array_map(
                    fn (VariantEntry $entry): array => [
                        'value' => $entry->value,
                        'language' => $entry->language,
                        'script' => $entry->script,
                        'type' => $entry->type->value,
                        'metadata' => $this->normalizeAssociativeArray($entry->metadata),
                    ],
                    $group->variants,
                ),
            ],
            $dataset->groups,
        );

        try {
            return json_encode(
                $data,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            )."\n";
        } catch (JsonException $e) {
            throw new InvalidResourceException('Unable to encode variant dataset JSON.', $e);
        }
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function normalizeAssociativeArray(array $values): array
    {
        ksort($values);

        foreach ($values as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $values[$key] = array_is_list($value)
                ? array_map(
                    fn (mixed $item): mixed => is_array($item) ? $this->normalizeNestedArray($item) : $item,
                    $value,
                )
                : $this->normalizeAssociativeArray($value);
        }

        return $values;
    }

    /** @param array<mixed> $value */
    private function normalizeNestedArray(array $value): array
    {
        if (array_is_list($value)) {
            return array_map(
                fn (mixed $item): mixed => is_array($item) ? $this->normalizeNestedArray($item) : $item,
                $value,
            );
        }

        /** @var array<string, mixed> $value */
        return $this->normalizeAssociativeArray($value);
    }
}
