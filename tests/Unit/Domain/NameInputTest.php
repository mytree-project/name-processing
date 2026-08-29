<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Domain;

use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Exception\InvalidInputException;
use PHPUnit\Framework\TestCase;

final class NameInputTest extends TestCase
{
    public function test_rejects_unicode_whitespace_only_value(): void
    {
        $this->expectException(InvalidInputException::class);

        new NameInput("\u{00A0}\u{2003}");
    }
}
