<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Application;

use MyTree\NameProcessing\Contracts\NameOperationInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\ProcessingResult;
use MyTree\NameProcessing\Exception\UnknownOperationException;

final class OperationRegistry
{
    /** @var array<string, NameOperationInterface> */
    private array $operations = [];

    /** @param iterable<NameOperationInterface> $operations */
    public function __construct(iterable $operations = [])
    {
        foreach ($operations as $operation) {
            $this->register($operation);
        }
    }

    public function register(NameOperationInterface $operation): void
    {
        $this->operations[$operation->name()] = $operation;
    }

    public function process(string $operation, NameInput $input, ?string $profileId = null): ProcessingResult
    {
        return $this->get($operation)->process($input, $profileId);
    }

    public function get(string $operation): NameOperationInterface
    {
        return $this->operations[$operation] ?? throw new UnknownOperationException($operation);
    }

    /** @return list<array{name: string, default_profile: string}> */
    public function describe(): array
    {
        $out = [];
        foreach ($this->operations as $operation) {
            $out[] = [
                'name' => $operation->name(),
                'default_profile' => $operation->defaultProfile(),
            ];
        }

        usort($out, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);

        return $out;
    }
}
