<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Folding;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Folding\IcuNameFolder;
use MyTree\NameProcessing\Profile\JsonProfileRepository;
use PHPUnit\Framework\TestCase;

final class IcuNameFolderTest extends TestCase
{
    public function test_collapses_unicode_edge_whitespace_before_trimming(): void
    {
        $profiles = new JsonProfileRepository(dirname(__DIR__, 3) . '/resources/profiles');
        $service = new IcuNameFolder($profiles);

        $result = $service->fold(new NameInput("\u{00A0}Józef\u{2003}"));

        self::assertSame('jozef', $result->firstValue());
    }

    public function test_folds_polish_diacritics_for_search(): void
    {
        $profiles = new JsonProfileRepository(dirname(__DIR__, 3) . '/resources/profiles');
        $service = new IcuNameFolder($profiles);

        $result = $service->fold(new NameInput(' Józef '));

        self::assertSame('jozef', $result->firstValue());
        self::assertSame('latin-search', $result->profile->id);
    }
}
