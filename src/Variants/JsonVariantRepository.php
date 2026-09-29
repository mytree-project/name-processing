<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Variants;

use MyTree\NameProcessing\Contracts\VariantRepositoryInterface;
use MyTree\NameProcessing\Domain\VariantDataset;
use MyTree\NameProcessing\Exception\InvalidResourceException;

final class JsonVariantRepository implements VariantRepositoryInterface
{
    public function __construct(
        private readonly string $baseDirectory,
        private readonly VariantDatasetJsonCodec $codec = new VariantDatasetJsonCodec(),
    ) {
    }

    public function load(string $datasetId): VariantDataset
    {
        if (! preg_match('/^[a-z0-9][a-z0-9._-]*$/', $datasetId)) {
            throw new InvalidResourceException("Unsafe dataset identifier: {$datasetId}");
        }

        $path = rtrim($this->baseDirectory, '/\\').'/'.$datasetId.'.json';
        if (! is_file($path)) {
            throw new InvalidResourceException("Variant dataset not found: {$datasetId}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new InvalidResourceException("Unable to read variant dataset: {$path}");
        }

        try {
            return $this->codec->decode($contents, $datasetId);
        } catch (InvalidResourceException $e) {
            throw new InvalidResourceException("Invalid variant dataset resource: {$path}", $e);
        }
    }
}
