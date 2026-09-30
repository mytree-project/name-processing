<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Application;

use MyTree\NameProcessing\Application\DefaultRegistryFactory;
use MyTree\NameProcessing\Contracts\VariantRepositoryInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\NameType;
use MyTree\NameProcessing\Domain\OutputValue;
use MyTree\NameProcessing\Domain\VariantDataset;
use MyTree\NameProcessing\Domain\VariantEntry;
use MyTree\NameProcessing\Domain\VariantGroup;
use PHPUnit\Framework\TestCase;

final class DefaultRegistryFactoryTest extends TestCase
{
    public function test_accepts_application_supplied_variant_repository(): void
    {
        $variants = new class () implements VariantRepositoryInterface {
            public function load(string $datasetId): VariantDataset
            {
                return new VariantDataset(
                    id: $datasetId,
                    version: 'application-1',
                    groups: [
                        new VariantGroup('custom-joseph', [
                            new VariantEntry('Józef', 'pl', 'Latn', NameType::GivenName),
                            new VariantEntry('Joseph', 'de', 'Latn', NameType::GivenName),
                        ]),
                    ],
                );
            }
        };

        $registry = DefaultRegistryFactory::create(dirname(__DIR__, 3), $variants);
        $result = $registry->process(
            'variants',
            new NameInput('Józef', type: NameType::GivenName),
            'genealogy-pl-ru',
        );

        self::assertSame('application-1', $result->metadata['dataset_version'] ?? null);
        self::assertSame(['Joseph'], array_map(
            static fn (OutputValue $value): string => $value->value,
            $result->results,
        ));
    }
}
