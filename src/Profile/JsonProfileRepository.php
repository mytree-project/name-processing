<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Profile;

use JsonException;
use MyTree\NameProcessing\Contracts\ProfileRepositoryInterface;
use MyTree\NameProcessing\Domain\Profile;
use MyTree\NameProcessing\Exception\InvalidResourceException;
use MyTree\NameProcessing\Exception\UnknownProfileException;

final class JsonProfileRepository implements ProfileRepositoryInterface
{
    private const SCHEMA = 'mytree.name-processing-profile.v1';

    public function __construct(private readonly string $baseDirectory)
    {
    }

    public function get(string $operation, string $profileId): Profile
    {
        $this->assertSafeIdentifier($operation, 'operation');
        $this->assertSafeIdentifier($profileId, 'profile');

        $path = rtrim($this->baseDirectory, '/\\') . '/' . $operation . '/' . $profileId . '.json';
        if (!is_file($path)) {
            throw new UnknownProfileException($operation, $profileId);
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new InvalidResourceException("Unable to read profile: {$path}");
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidResourceException("Invalid profile JSON: {$path}", $e);
        }

        if (!is_array($data)) {
            throw new InvalidResourceException("Profile must decode to an object: {$path}");
        }

        $schema = $data['schema'] ?? null;
        $id = $data['id'] ?? null;
        $version = $data['version'] ?? null;
        $profileOperation = $data['operation'] ?? null;
        $config = $data['config'] ?? null;

        if ($schema !== self::SCHEMA) {
            throw new InvalidResourceException("Unsupported profile schema in: {$path}");
        }

        if (
            $id !== $profileId
            || $profileOperation !== $operation
            || !is_string($version)
            || $version === ''
            || !is_array($config)
        ) {
            throw new InvalidResourceException("Profile metadata does not match path: {$path}");
        }

        return new Profile($id, $version, $profileOperation, $config);
    }

    private function assertSafeIdentifier(string $value, string $kind): void
    {
        if (!preg_match('/^[a-z0-9][a-z0-9._-]*$/', $value)) {
            throw new InvalidResourceException("Unsafe {$kind} identifier: {$value}");
        }
    }
}
