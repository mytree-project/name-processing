<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Cli;

use JsonException;
use MyTree\NameProcessing\Application\OperationRegistry;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\NameType;
use MyTree\NameProcessing\Exception\InvalidInputException;
use MyTree\NameProcessing\Exception\NameProcessingException;
use Throwable;

final readonly class CliApplication
{
    private const HELP_SCHEMA = 'mytree.name-processing.cli-help.v1';
    private const ERROR_SCHEMA = 'mytree.name-processing.error.v1';

    public function __construct(private OperationRegistry $registry)
    {
    }

    /** @param list<string> $argv */
    public function run(array $argv): int
    {
        try {
            $parsed = $this->parse(array_slice($argv, 1));
            if ($parsed['help']) {
                $this->write(STDOUT, $this->helpPayload());

                return 0;
            }

            $operation = $parsed['operation'];
            $options = $parsed['options'];
            $value = $parsed['value'];

            $type = null;
            if (isset($options['type'])) {
                if (!is_string($options['type'])) {
                    throw new InvalidInputException('--type requires a value.');
                }

                $type = NameType::tryFrom($options['type']);
                if ($type === null) {
                    throw new InvalidInputException('Unsupported --type. Use given_name, surname, place_name or other.');
                }
            } elseif ($operation === 'variants') {
                $type = NameType::GivenName;
            }

            $input = new NameInput(
                value: $value,
                language: $this->optionalString($options, 'language'),
                script: $this->optionalString($options, 'script'),
                type: $type,
            );

            $profile = $this->optionalString($options, 'profile');
            $result = $this->registry->process($operation, $input, $profile);
            $this->write(STDOUT, $result);

            return 0;
        } catch (NameProcessingException $e) {
            $this->write(STDERR, [
                'schema' => self::ERROR_SCHEMA,
                'error' => [
                    'code' => $e->errorCode(),
                    'message' => $e->getMessage(),
                ],
            ]);

            return $this->exitCodeFor($e);
        } catch (Throwable $e) {
            $this->write(STDERR, [
                'schema' => self::ERROR_SCHEMA,
                'error' => [
                    'code' => 'internal_error',
                    'message' => $e->getMessage(),
                ],
            ]);

            return 1;
        }
    }

    /**
     * @param list<string> $args
     * @return array{help: bool, operation: string, options: array<string, string|bool>, value: string}
     */
    private function parse(array $args): array
    {
        if ($args === [] || in_array($args[0], ['help', '--help', '-h'], true)) {
            return ['help' => true, 'operation' => '', 'options' => [], 'value' => ''];
        }

        $operation = array_shift($args);
        if ($operation === '') {
            throw new InvalidInputException('Missing operation.');
        }

        $allowed = ['profile', 'language', 'script', 'type', 'value', 'help'];
        $options = [];
        $positionals = [];

        for ($i = 0, $count = count($args); $i < $count; ++$i) {
            $arg = $args[$i];
            if (!str_starts_with($arg, '--')) {
                $positionals[] = $arg;
                continue;
            }

            $raw = substr($arg, 2);
            if ($raw === '') {
                throw new InvalidInputException('Invalid empty option.');
            }

            if (str_contains($raw, '=')) {
                [$key, $value] = explode('=', $raw, 2);
                $options[$key] = $value;
            } else {
                $key = $raw;
                $next = $args[$i + 1] ?? null;
                if ($key !== 'help' && is_string($next) && !str_starts_with($next, '--')) {
                    $options[$key] = $next;
                    ++$i;
                } else {
                    $options[$key] = true;
                }
            }

            if (!in_array($key, $allowed, true)) {
                throw new InvalidInputException("Unknown option: --{$key}");
            }
        }

        if (isset($options['help'])) {
            return ['help' => true, 'operation' => '', 'options' => [], 'value' => ''];
        }

        $valueOption = $options['value'] ?? null;
        if ($valueOption !== null && !is_string($valueOption)) {
            throw new InvalidInputException('--value requires a value.');
        }

        if ($valueOption !== null && $positionals !== []) {
            throw new InvalidInputException('Provide the name either positionally or through --value, not both.');
        }

        if ($valueOption === null && count($positionals) !== 1) {
            throw new InvalidInputException('Exactly one name value is required.');
        }

        return [
            'help' => false,
            'operation' => $operation,
            'options' => $options,
            'value' => $valueOption ?? $positionals[0],
        ];
    }

    /** @param array<string, string|bool> $options */
    private function optionalString(array $options, string $key): ?string
    {
        if (!array_key_exists($key, $options)) {
            return null;
        }

        $value = $options[$key];
        if (!is_string($value) || trim($value) === '') {
            throw new InvalidInputException("--{$key} requires a non-empty value.");
        }

        return trim($value);
    }

    private function exitCodeFor(NameProcessingException $exception): int
    {
        return in_array($exception->errorCode(), [
            'invalid_input',
            'unknown_operation',
            'unknown_profile',
            'invalid_profile',
        ], true) ? 2 : 1;
    }

    /** @return array<string, mixed> */
    private function helpPayload(): array
    {
        return [
            'schema' => self::HELP_SCHEMA,
            'usage' => 'php bin/mytree-name OPERATION [--profile=PROFILE] [--language=LANG] [--script=SCRIPT] [--type=TYPE] "VALUE"',
            'output_format' => 'json',
            'operations' => $this->registry->describe(),
            'types' => array_map(static fn (NameType $type): string => $type->value, NameType::cases()),
        ];
    }

    /** @param resource $stream */
    private function write($stream, mixed $payload): void
    {
        try {
            $json = json_encode(
                $payload,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            throw new NameProcessingException('Unable to encode JSON output.', 'json_error', $e);
        }

        fwrite($stream, $json . PHP_EOL);
    }
}
