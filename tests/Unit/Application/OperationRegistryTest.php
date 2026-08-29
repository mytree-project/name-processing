<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Application;

use MyTree\NameProcessing\Application\OperationRegistry;
use MyTree\NameProcessing\Exception\UnknownOperationException;
use PHPUnit\Framework\TestCase;

final class OperationRegistryTest extends TestCase
{
    public function test_unknown_operation_is_explicit_error(): void
    {
        $registry = new OperationRegistry();

        $this->expectException(UnknownOperationException::class);
        $registry->get('missing');
    }
}
