<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Variants;

use MyTree\NameProcessing\Exception\InvalidResourceException;
use MyTree\NameProcessing\Variants\JsonVariantRepository;
use PHPUnit\Framework\TestCase;

final class JsonVariantRepositoryTest extends TestCase
{
    public function test_rejects_unsupported_dataset_schema(): void
    {
        $repository = new JsonVariantRepository(dirname(__DIR__, 2) . '/fixtures/variants');

        $this->expectException(InvalidResourceException::class);
        $repository->load('unsupported-schema');
    }

    public function test_loads_versioned_dataset(): void
    {
        $repository = new JsonVariantRepository(dirname(__DIR__, 3) . '/resources/variants');

        $dataset = $repository->load('given-names.v1');

        self::assertSame('given-names.v1', $dataset->id);
        self::assertSame('1.0.0', $dataset->version);
        self::assertNotEmpty($dataset->groups);
    }
}
