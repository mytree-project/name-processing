<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Tests\Integration\Cli;

use PHPUnit\Framework\TestCase;

final class MyTreeNameCliTest extends TestCase
{
    public function test_normalize_command_returns_versioned_json(): void
    {
        $result = $this->runCli(['normalize', ' JÓZEF ']);

        self::assertSame(0, $result['exit']);
        $json = json_decode($result['stdout'], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('mytree.name-processing.v1', $json['schema']);
        self::assertSame('normalize', $json['operation']);
        self::assertSame('józef', $json['results'][0]['value']);
        self::assertSame('', $result['stderr']);
    }

    public function test_help_is_json(): void
    {
        $result = $this->runCli(['help']);

        self::assertSame(0, $result['exit']);
        $json = json_decode($result['stdout'], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('mytree.name-processing.cli-help.v1', $json['schema']);
        self::assertSame('json', $json['output_format']);
    }

    public function test_unknown_operation_returns_json_error_and_exit_two(): void
    {
        $result = $this->runCli(['does-not-exist', 'Józef']);

        self::assertSame(2, $result['exit']);
        self::assertSame('', $result['stdout']);
        $json = json_decode($result['stderr'], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('mytree.name-processing.error.v1', $json['schema']);
        self::assertSame('unknown_operation', $json['error']['code']);
    }

    /** @param list<string> $arguments @return array{exit: int, stdout: string, stderr: string} */
    private function runCli(array $arguments): array
    {
        $root = dirname(__DIR__, 3);
        $command = array_merge([PHP_BINARY, $root . '/bin/mytree-name'], $arguments);

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, $root);
        self::assertIsResource($process);

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        return [
            'exit' => $exit,
            'stdout' => $stdout === false ? '' : $stdout,
            'stderr' => $stderr === false ? '' : $stderr,
        ];
    }
}
