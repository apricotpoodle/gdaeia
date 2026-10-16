<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\Integrity\CommentsIntegrityChecker;
use App\Service\Integrity\IntegrityAuditService;
use App\Service\Integrity\ReferenceIntegrityChecker;
use App\Service\Integrity\TreeIntegrityCheckAdapter;
use App\Service\Integrity\ValidationWorkflowIntegrityChecker;
use App\Service\Integrity\ViewsIntegrityChecker;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Closure;
use Throwable;

/** Exécute le diagnostic transversal d’intégrité, sans mutation. */
final class IntegrityAuditCommand extends Command
{
    private const EXIT_ANOMALY = 1;
    private const EXIT_TECHNICAL = 2;

    /**
     * @var \Closure(string): array<string, mixed>
     */
    private readonly Closure $audit;

    /**
     * @param \Cake\Console\CommandFactoryInterface|null $factory Fabrique CakePHP.
     * @param \Closure(string): array<string, mixed>|null $audit Audit injectable pour les tests.
     */
    public function __construct(?CommandFactoryInterface $factory = null, ?Closure $audit = null)
    {
        parent::__construct($factory);
        $this->audit = $audit ?? static function (string $scope): array {
            $service = new IntegrityAuditService([
                new ReferenceIntegrityChecker(),
                new ValidationWorkflowIntegrityChecker(),
                new TreeIntegrityCheckAdapter(TableRegistry::getTableLocator()),
                new CommentsIntegrityChecker(),
                new ViewsIntegrityChecker(),
            ]);

            return $service->audit($scope);
        };
    }

    /** @param \Cake\Console\ConsoleOptionParser $parser Analyseur d’options. */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(
                'Audite les références, workflows, arbres, commentaires et vues sans modifier les données.',
            )
            ->addOption('scope', [
                'help' => 'Périmètre : all, references, workflow, trees, comments ou views.',
                'default' => 'all',
                'choices' => ['all', 'references', 'workflow', 'trees', 'comments', 'views'],
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
        $scope = (string)$args->getOption('scope');
        try {
            $report = ($this->audit)($scope);
        } catch (Throwable $exception) {
            if ($args->getOption('format') === 'json') {
                $io->out((string)json_encode([
                    'success' => false,
                    'error' => $exception->getMessage(),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            } else {
                $io->error($exception->getMessage());
            }

            return self::EXIT_TECHNICAL;
        }

        if ($args->getOption('format') === 'json') {
            $io->out((string)json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } else {
            $summary = $report['summary'];
            $io->out(sprintf(
                'Contrôles : %d ; lignes analysées : %d ; erreurs : %d ; avertissements : %d.',
                $summary['checks'],
                $summary['rows_scanned'],
                $summary['errors'],
                $summary['warnings'],
            ));
            foreach ($report['reports'] as $check) {
                $io->{$check['status'] === 'ok' ? 'success' : 'error'}(sprintf(
                    '%s : %s (%d violation(s)).',
                    $check['id'],
                    $check['status'],
                    $check['violations'],
                ));
            }
        }

        return $report['success'] ? static::CODE_SUCCESS : self::EXIT_ANOMALY;
    }
}
