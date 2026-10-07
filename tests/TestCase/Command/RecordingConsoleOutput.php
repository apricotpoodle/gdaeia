<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\Console\ConsoleOutput;

/** Sortie mémoire sans détection TTY, adaptée aux tests de commandes. */
final class RecordingConsoleOutput extends ConsoleOutput
{
    public string $contents = '';

    public function __construct()
    {
    }

    public function write(array|string $message, int $newlines = 1): int
    {
        $text = is_array($message) ? implode(PHP_EOL, $message) : $message;
        $text .= str_repeat(PHP_EOL, $newlines);
        $this->contents .= $text;

        return strlen($text);
    }
}
