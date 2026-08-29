<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Transliteration;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Profile\JsonProfileRepository;
use MyTree\NameProcessing\Transliteration\IcuTransliterator;
use PHPUnit\Framework\TestCase;

final class IcuTransliteratorTest extends TestCase
{
    public function test_transliterates_cyrillic_name_to_latin(): void
    {
        $profiles = new JsonProfileRepository(dirname(__DIR__, 3) . '/resources/profiles');
        $service = new IcuTransliterator($profiles);

        $result = $service->transliterate(new NameInput('Иосиф', language: 'ru', script: 'Cyrl'));

        self::assertSame('Iosif', $result->firstValue());
        self::assertSame('Latn', $result->results[0]->script);
        self::assertSame('cyrillic-latin', $result->profile->id);
    }
}
