<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\Console\ConsoleIo;
use Cake\TestSuite\TestCase;

/** Outils communs pour capturer les sorties des commandes CLI. */
abstract class CommandTestCase extends TestCase
{
    /**
     * @return array{io: \Cake\Console\ConsoleIo, out: \App\Test\TestCase\Command\RecordingConsoleOutput, err: \App\Test\TestCase\Command\RecordingConsoleOutput}
     */
    protected function createIo(): array
    {
        $out = new RecordingConsoleOutput();
        $err = new RecordingConsoleOutput();

        return [
            'io' => new ConsoleIo($out, $err),
            'out' => $out,
            'err' => $err,
        ];
    }

    protected function streamContents(RecordingConsoleOutput $output): string
    {
        return $output->contents;
    }
}
