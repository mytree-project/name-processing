<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Variants;

use JsonException;
use MyTree\NameProcessing\Contracts\VariantRepositoryInterface;
use MyTree\NameProcessing\Domain\NameType;
use MyTree\NameProcessing\Domain\VariantDataset;
use MyTree\NameProcessing\Domain\VariantEntry;
use MyTree\NameProcessing\Domain\VariantGroup;
use MyTree\NameProcessing\Exception\InvalidResourceException;

final class JsonVariantRepository implements VariantRepositoryInterface
{
    private const SCHEMA = 'mytree.name-variants.v1';

    public function __construct(private readonly string $baseDirectory)
    {
    }

    public function load(string $datasetId): VariantDataset
    {
        if (!preg_match('/^[a-z0-9][a-z0-9._-]*$/', $datasetId)) {
            throw new InvalidResourceException("Unsafe dataset identifier: {$datasetId}");
        }

        $path = rtrim($this->baseDirectory, '/\\') . '/' . $datasetId . '.json';
        if (!is_file($path)) {
            throw new InvalidResourceException("Variant dataset not found: {$datasetId}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new InvalidResourceException("Unable to read variant dataset: {$path}");
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidResourceException("Invalid variant dataset JSON: {$path}", $e);
        }

        if (!is_array($data)) {
            throw new InvalidResourceException("Variant dataset must decode to an object: {$path}");
        }

        if (($data['schema'] ?? null) !== self::SCHEMA) {
            throw new InvalidResourceException("Unsupported variant dataset schema in: {$path}");
        }

        $version = $data['version'] ?? null;
        $groupsData = $data['groups'] ?? null;
        if (($data['id'] ?? null) !== $datasetId || !is_string($version) || $version === '' || !is_array($groupsData)) {
            throw new InvalidResourceException("Invalid dataset metadata: {$path}");
        }

        $groups = [];
        foreach ($groupsData as $groupData) {
            if (!is_array($groupData) || !is_string($groupData['id'] ?? null) || !is_array($groupData['variants'] ?? null)) {
                throw new InvalidResourceException("Invalid variant group in: {$path}");
            }

            $variants = [];
            foreach ($groupData['variants'] as $entryData) {
                if (!is_array($entryData) || !is_string($entryData['value'] ?? null)) {
                    throw new InvalidResourceException("Invalid variant entry in: {$path}");
                }

                $typeRaw = $entryData['type'] ?? null;
                $type = is_string($typeRaw) ? NameType::tryFrom($typeRaw) : null;
                if ($type === null) {
                    throw new InvalidResourceException("Invalid variant type in: {$path}");
                }

                $metadata = $entryData['metadata'] ?? [];
                if (!is_array($metadata)) {
                    throw new InvalidResourceException("Variant metadata must be an object in: {$path}");
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

        return new VariantDataset($datasetId, $version, $groups);
    }
}
