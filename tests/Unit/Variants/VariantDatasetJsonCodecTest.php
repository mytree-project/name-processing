<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Variants;

use MyTree\NameProcessing\Exception\InvalidResourceException;
use MyTree\NameProcessing\Variants\VariantDatasetJsonCodec;
use PHPUnit\Framework\TestCase;

final class VariantDatasetJsonCodecTest extends TestCase
{
    public function test_round_trips_native_dataset_without_losing_top_level_metadata(): void
    {
        $json = file_get_contents(dirname(__DIR__, 3).'/resources/variants/given-names.v1.json');
        self::assertIsString($json);

        $codec = new VariantDatasetJsonCodec();
        $dataset = $codec->decode($json, 'given-names.v1');

        self::assertSame('mvp_seed_demonstration', $dataset->metadata['status'] ?? null);

        $encoded = $codec->encode($dataset);
        $roundTripped = $codec->decode($encoded, 'given-names.v1');

        self::assertEquals($dataset, $roundTripped);
        self::assertSame($encoded, $codec->encode($roundTripped));
    }

    public function test_rejects_unsupported_schema(): void
    {
        $codec = new VariantDatasetJsonCodec();

        $this->expectException(InvalidResourceException::class);
        $codec->decode('{"schema":"unsupported","id":"example","version":"1","groups":[]}');
    }

    public function test_rejects_unexpected_dataset_id(): void
    {
        $codec = new VariantDatasetJsonCodec();

        $this->expectException(InvalidResourceException::class);
        $codec->decode(
            '{"schema":"mytree.name-variants.v1","id":"actual","version":"1","groups":[]}',
            'expected',
        );
    }
}
