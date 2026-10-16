<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\ValidationWorkflowSequenceRepairService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Closure;
use Throwable;

/** Détecte et répare les références orphelines des étapes de validation. */
final class ValidationWorkflowSequenceRepairCommand extends Command
{
    /**
     * @var \Closure(bool): array<string, mixed>
     */
    private readonly Closure $repair;

    /**
     * @param \Cake\Console\CommandFactoryInterface|null $factory Fabrique CakePHP.
     * @param \Closure(bool): array<string, mixed>|null $repair Réparateur injectable.
     */
    public function __construct(?CommandFactoryInterface $factory = null, ?Closure $repair = null)
    {
        parent::__construct($factory);
        $this->repair = $repair ?? static function (bool $apply): array {
            return (new ValidationWorkflowSequenceRepairService())->repair($apply);
        };
    }

    /** @param \Cake\Console\ConsoleOptionParser $parser Analyseur d’options. */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription('Détecte et réaligne les références orphelines des étapes de validation.')
            ->addOption('apply', [
                'help' => 'Applique les réalignements dont la correspondance est unique.',
                'boolean' => true,
            ])
            ->addOption('format', [
                'help' => 'Format de sortie : text ou json.',
                'default' => 'text',
                'choices' => ['text', 'json'],
            ]);
    }

    /** @param \Cake\Console\Arguments $args Arguments CLI. */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $apply = (bool)$args->getOption('apply');
        try {
            $report = ($this->repair)($apply);
        } catch (Throwable $exception) {
            $io->error($exception->getMessage());

            return static::CODE_ERROR;
        }

        if ($args->getOption('format') === 'json') {
            $io->out((string)json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } else {
            $io->out($apply ? 'Mode application.' : 'Mode diagnostic.');
            $io->out(sprintf(
                'Étapes orphelines : %d ; réparables : %d ; réparées : %d ; ignorées : %d.',
                $report['scanned'],
                $report['repairable'],
                $report['repaired'],
                $report['skipped'],
            ));
            foreach ($report['items'] as $item) {
                if ((int)$item['matching_sequences'] === 1) {
                    continue;
                }
                $io->warning(sprintf(
                    'Étape %d ignorée : %d séquence(s) active(s) correspondante(s).',
                    $item['step_id'],
                    $item['matching_sequences'],
                ));
            }
        }

        return (int)$report['skipped'] === 0 ? static::CODE_SUCCESS : static::CODE_ERROR;
    }
}
