<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Normalization;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Normalization\IcuNameNormalizer;
use MyTree\NameProcessing\Profile\JsonProfileRepository;
use PHPUnit\Framework\TestCase;

final class IcuNameNormalizerTest extends TestCase
{
    public function test_collapses_unicode_edge_whitespace_before_trimming(): void
    {
        $profiles = new JsonProfileRepository(dirname(__DIR__, 3) . '/resources/profiles');
        $normalizer = new IcuNameNormalizer($profiles);

        $result = $normalizer->normalize(new NameInput("\u{00A0}Józef\u{2003}"));

        self::assertSame('józef', $result->firstValue());
    }

    public function test_normalizes_unicode_case_and_whitespace(): void
    {
        $profiles = new JsonProfileRepository(dirname(__DIR__, 3) . '/resources/profiles');
        $normalizer = new IcuNameNormalizer($profiles);

        $result = $normalizer->normalize(new NameInput("  JÓZEF\t  NOWAK  "));

        self::assertSame('józef nowak', $result->firstValue());
        self::assertSame('default', $result->profile->id);
        self::assertSame('1.0.0', $result->profile->version);
    }
}
