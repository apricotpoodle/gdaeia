<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\Department;
use App\Service\Migration\DepartmentImportService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use RuntimeException;

/**
 * Importe les sociétés, services et codes CGR depuis les exports historiques.
 */
final class MigrationImportDepartmentsCommand extends Command
{
    /**
     * Définit les options de la commande.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Analyseur de commande.
     * @return \Cake\Console\ConsoleOptionParser Analyseur configuré.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription('Prépare ou importe les sociétés, services et codes CGR historiques.')
            ->addOption('source-dir', [
                'help' => 'Répertoire contenant les exports CSV.',
                'short' => 's',
                'default' => null,
            ])
            ->addOption('report-dir', [
                'help' => 'Répertoire de sortie du rapport des services non rapprochés.',
                'short' => 'r',
                'default' => null,
            ])
            ->addOption('dry-run', [
                'help' => 'Affiche le plan sans accéder à la base.',
                'boolean' => true,
            ])
            ->addOption('apply', [
                'help' => 'Applique le plan dans une transaction.',
                'boolean' => true,
            ]);
    }

    /**
     * Prépare ou applique l'import.
     *
     * @param \Cake\Console\Arguments $args Arguments de la commande.
     * @param \Cake\Console\ConsoleIo $io Sortie console.
     * @return int Code de sortie.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $apply = (bool)$args->getOption('apply');
        $dryRun = (bool)$args->getOption('dry-run');
        if ($apply && $dryRun) {
            $io->error('Les options --apply et --dry-run sont exclusives.');

            return static::CODE_ERROR;
        }

        $sourceDirectory = $args->getOption('source-dir')
            ?? env('MIGRATION_EXPORT_DIR', TMP . 'migration' . DIRECTORY_SEPARATOR . 'exports');
        $reportDirectory = $args->getOption('report-dir');

        try {
            $plan = (new DepartmentImportService())->prepare((string)$sourceDirectory);
            if ($reportDirectory !== null) {
                $this->writeUnmappedReport((string)$reportDirectory, $plan['unmapped_services']);
            }
            $this->writeSummary($io, $plan, $apply);
            if (!$apply) {
                return static::CODE_SUCCESS;
            }

            $result = $this->applyPlan($plan);
            $io->out(sprintf(
                'Import appliqué : %d sociétés, %d services, %d CGR.',
                $result['companies'],
                $result['services'],
                $result['cgrs'],
            ));
        } catch (RuntimeException $exception) {
            $io->error($exception->getMessage());

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }

    /**
     * @param array{
     *     companies: list<array{source_id: string, code: string, name: string, order: string}>,
     *     services: list<array{
     *         source_company_id: string,
     *         company_code: string,
     *         source_service_id: string,
     *         source_service_code: string,
     *         name: string,
     *         target_code: string,
     *         cgrs: list<array{source_id: string, code: string, label: string, type: string}>
     *     }>,
     *     cgr_count: int,
     *     cgr_assignment_count: int,
     *     unmapped_service_count: int,
     *     unmapped_services: list<array{source_id: string, code: string, name: string}>
     * } $plan
     */
    private function writeSummary(ConsoleIo $io, array $plan, bool $apply): void
    {
        $summary = sprintf(
            '%sSociétés : %d ; Services rapprochés : %d ; CGR distincts : %d ; '
            . 'Affectations CGR : %d ; Services non rapprochés : %d.',
            $apply ? 'Plan d’import — ' : 'Mode simulation — ',
            count($plan['companies']),
            count($plan['services']),
            $plan['cgr_count'],
            $plan['cgr_assignment_count'],
            $plan['unmapped_service_count'],
        );
        $io->out($summary);
    }

    /**
     * @param array{
     *     companies: list<array{source_id: string, code: string, name: string, order: string}>,
     *     services: list<array{
     *         source_company_id: string,
     *         company_code: string,
     *         source_service_id: string,
     *         source_service_code: string,
     *         name: string,
     *         target_code: string,
     *         cgrs: list<array{source_id: string, code: string, label: string, type: string}>
     *     }>,
     *     cgr_count: int,
     *     unmapped_service_count: int,
     *     unmapped_services: list<array{source_id: string, code: string, name: string}>
     * } $plan
     * @return array{companies: int, services: int, cgrs: int}
     */
    private function applyPlan(array $plan): array
    {
        $departments = TableRegistry::getTableLocator()->get('Departments');
        $strategies = TableRegistry::getTableLocator()->get('CgrStrategies');
        $cgrCodes = TableRegistry::getTableLocator()->get('CgrCodes');

        return $departments->getConnection()->transactional(function () use (
            $departments,
            $strategies,
            $cgrCodes,
            $plan,
        ): array {
            $sem = $this->findSingleDepartment($departments, 'SEM');
            $strategy = $strategies->find()->where(['code' => 'SEM'])->first();
            if ($strategy === null || !$this->hasServiceStrategy($strategy->get('definition_json'))) {
                throw new RuntimeException(
                    'La stratégie CGR SEM doit exister et contenir une zone SERVICE.',
                );
            }

            $targetCompanies = ['SEM' => $sem];
            foreach ($plan['companies'] as $company) {
                if ($company['code'] === 'SEM') {
                    continue;
                }
                $entity = $this->findOrCreateDepartment(
                    $departments,
                    $company['code'],
                    [
                        'parent_id' => null,
                        'base' => true,
                        'code' => $company['code'],
                        'name' => $company['name'],
                        'sort' => $company['order'],
                        'department_type_id' => 1,
                        'cgr_strategy_id' => null,
                    ],
                );
                $targetCompanies[$company['code']] = $entity;
            }

            $serviceCount = 0;
            $cgrCount = 0;
            foreach ($plan['services'] as $service) {
                $parent = $targetCompanies[$service['company_code']] ?? null;
                if ($parent === null) {
                    throw new RuntimeException(
                        sprintf('Société cible absente pour le service %s.', $service['target_code']),
                    );
                }
                $department = $this->findOrCreateDepartment(
                    $departments,
                    $service['target_code'],
                    [
                        'parent_id' => $parent->get('id'),
                        'base' => true,
                        'code' => $service['target_code'],
                        'name' => $service['name'],
                        'sort' => $service['source_service_code'],
                        'department_type_id' => 1,
                        'cgr_strategy_id' => $service['company_code'] === 'SEM'
                            ? $strategy->get('id')
                            : null,
                    ],
                );
                $serviceCount++;

                foreach ($service['cgrs'] as $cgr) {
                    $code = $cgrCodes->find()
                        ->where([
                            'department_id' => $department->get('id'),
                            'type' => $cgr['type'],
                            'code' => $cgr['code'],
                        ])
                        ->first();
                    if ($code === null) {
                        $code = $cgrCodes->newEntity([
                            'department_id' => $department->get('id'),
                            'type' => $cgr['type'],
                            'code' => $cgr['code'],
                            'label' => $cgr['label'],
                            'active' => true,
                            'is_system' => false,
                        ]);
                    } else {
                        $code->set('label', $cgr['label']);
                        $code->set('active', true);
                    }
                    $cgrCodes->saveOrFail($code);
                    $cgrCount++;
                }
            }

            return [
                'companies' => count($targetCompanies),
                'services' => $serviceCount,
                'cgrs' => $cgrCount,
            ];
        });
    }

    /** @return \App\Model\Entity\Department Département cible unique. */
    private function findSingleDepartment(Table $departments, string $code): Department
    {
        $matches = $departments->find()
            ->where(['code' => $code, 'deleted IS' => null])
            ->all()
            ->toList();
        if (count($matches) !== 1) {
            throw new RuntimeException(
                sprintf('Le département racine %s doit exister une seule fois.', $code),
            );
        }

        /** @var \App\Model\Entity\Department $department */
        $department = $matches[0];

        return $department;
    }

    /** @param array<string, mixed> $data */
    private function findOrCreateDepartment(Table $departments, string $code, array $data): Department
    {
        $entity = $departments->find()->where(['code' => $code])->first();
        if ($entity === null) {
            $entity = $departments->newEntity($data);
        } else {
            if ($entity->get('parent_id') !== $data['parent_id']) {
                throw new RuntimeException(sprintf('Le département %s existe avec un parent différent.', $code));
            }
            foreach ($data as $field => $value) {
                $entity->set($field, $value);
            }
        }

        /** @var \App\Model\Entity\Department $saved */
        $saved = $departments->saveOrFail($entity);

        return $saved;
    }

    /** @param mixed $definition */
    private function hasServiceStrategy(mixed $definition): bool
    {
        if (is_string($definition)) {
            $definition = json_decode($definition, true);
        }
        if (!is_array($definition) || count($definition) !== 1) {
            return false;
        }
        $item = reset($definition);
        $type = is_array($item) ? ($item['type'] ?? $item['code'] ?? '') : $item;

        return strtoupper(trim((string)$type)) === 'SERVICE';
    }

    /**
     * @param list<array{source_id: string, code: string, name: string}> $services
     */
    private function writeUnmappedReport(string $directory, array $services): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le répertoire du rapport.');
        }
        $path = $directory . DIRECTORY_SEPARATOR . 'unmapped-services.csv';
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Impossible d’écrire le rapport des services.');
        }
        fputcsv($handle, ['source_service_id', 'source_service_code', 'source_service_name']);
        foreach ($services as $service) {
            fputcsv($handle, [$service['source_id'], $service['code'], $service['name']]);
        }
        fclose($handle);
    }
}
