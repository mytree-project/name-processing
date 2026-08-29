<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Unit\Profile;

use MyTree\NameProcessing\Exception\InvalidResourceException;
use MyTree\NameProcessing\Profile\JsonProfileRepository;
use PHPUnit\Framework\TestCase;

final class JsonProfileRepositoryTest extends TestCase
{
    public function test_rejects_unsupported_profile_schema(): void
    {
        $repository = new JsonProfileRepository(dirname(__DIR__, 2) . '/fixtures/profiles');

        $this->expectException(InvalidResourceException::class);
        $repository->get('normalize', 'unsupported-schema');
    }
}
