<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\TableRegistry;

class CgrResolverService
{
    /**
     * Retourne la structure et les listes de choix CGR pour un département
     *
     * @param int $departmentId
     * @return array{strategy: string, schema: array, options: array}
     */
    public function getCgrConfigForDepartment(int $departmentId): array
    {
        $departmentsTable = TableRegistry::getTableLocator()->get('Departments');
        $cgrCodesTable = TableRegistry::getTableLocator()->get('CgrCodes');

        $department = $departmentsTable->get($departmentId, contain: ['CgrStrategies']);

        if (!$department->cgr_strategy) {
            return [
                'strategy' => 'FREE',
                'schema' => [],
                'options' => [],
            ];
        }

        // 1. Décodage du JSON de la stratégie
        $rawDefinition = json_decode((string)$department->cgr_strategy->definition_json, true) ?? [];

        $schema = [];
        foreach ($rawDefinition as $item) {
            $type = is_array($item) ? ($item['type'] ?? $item['code'] ?? '') : (string)$item;
            if ($type !== '') {
                $schema[] = strtoupper(trim($type));
            }
        }

        // 2. Récupération de TOUS les codes actifs pour ce département
        $codes = $cgrCodesTable->find()
            ->where([
                'department_id' => $departmentId,
                'active' => true,
            ])
            ->orderBy(['type' => 'ASC', 'code' => 'ASC'])
            ->all();

        // 3. Indexation flexible : on alimente le tableau sous la clé exacte en Majuscules
        $options = [];
        foreach ($codes as $code) {
            $typeKey = strtoupper(trim($code->type));
            
            if (!isset($options[$typeKey])) {
                $options[$typeKey] = [];
            }

            $options[$typeKey][] = [
                'code' => $code->code,
                'label' => sprintf('%s - %s', $code->code, $code->label),
            ];
        }

        return [
            'strategy' => $department->cgr_strategy->code,
            'schema' => $schema,
            'options' => $options,
        ];
    }
}