<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Variants;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\OutputValue;
use MyTree\NameProcessing\Normalization\IcuNameNormalizer;
use MyTree\NameProcessing\Profile\JsonProfileRepository;
use MyTree\NameProcessing\Variants\JsonVariantRepository;
use MyTree\NameProcessing\Variants\NameVariantResolver;
use PHPUnit\Framework\TestCase;

final class NameVariantResolverTest extends TestCase
{
    public function test_resolves_seed_language_equivalent_candidates(): void
    {
        $root = dirname(__DIR__, 3);
        $profiles = new JsonProfileRepository($root . '/resources/profiles');
        $normalizer = new IcuNameNormalizer($profiles);
        $repository = new JsonVariantRepository($root . '/resources/variants');
        $resolver = new NameVariantResolver($profiles, $repository, $normalizer);

        $result = $resolver->resolve(new NameInput('Иосиф', language: 'ru', script: 'Cyrl'));
        $values = array_map(static fn (OutputValue $item): string => $item->value, $result->results);

        self::assertContains('Józef', $values);
        self::assertContains('Iosif', $values);
        self::assertNotContains('Иосиф', $values);
        self::assertSame('given-names.v1', $result->metadata['dataset_id']);
        self::assertSame(['joseph'], $result->metadata['matched_groups']);
    }

    public function test_unknown_name_returns_empty_candidates_not_error(): void
    {
        $root = dirname(__DIR__, 3);
        $profiles = new JsonProfileRepository($root . '/resources/profiles');
        $normalizer = new IcuNameNormalizer($profiles);
        $repository = new JsonVariantRepository($root . '/resources/variants');
        $resolver = new NameVariantResolver($profiles, $repository, $normalizer);

        $result = $resolver->resolve(new NameInput('NieistniejąceImię'));

        self::assertSame([], $result->results);
        self::assertSame([], $result->metadata['matched_groups']);
    }
}
