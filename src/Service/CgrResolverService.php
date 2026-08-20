<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\TableRegistry;

class CgrResolverService
{
    /**
     * Retourne la structure et les choix CGR pour un département donné.
     *
     * @param int $departmentId
     * @return array{strategy: string, schema: array, options: array}
     */
    public function getCgrConfigForDepartment(int $departmentId): array
    {
        $departmentsTable = TableRegistry::getTableLocator()->get('Departments');
        $cgrCodesTable = TableRegistry::getTableLocator()->get('CgrCodes');

        /** @var \App\Model\Entity\Department $department */
        $department = $departmentsTable->get($departmentId, contain: ['CgrStrategies']);

        if (!$department->cgr_strategy) {
            return [
                'strategy' => 'FREE',
                'schema' => [],
                'options' => [],
            ];
        }

        // 1. Extraction et nettoyage du schéma de la stratégie
        $rawDefinition = json_decode((string)$department->cgr_strategy->definition_json, true) ?? [];

        $schema = [];
        foreach ($rawDefinition as $item) {
            $type = is_array($item) ? ($item['type'] ?? $item['code'] ?? '') : (string)$item;
            $cleanType = strtoupper(trim($type));
            if ($cleanType !== '') {
                $schema[] = $cleanType;
            }
        }

        // 2. Récupération des codes avec des clés d'ORM propres (CgrCodes)
        $codes = $cgrCodesTable->find()
            ->where([
                'CgrCodes.department_id' => $departmentId,
                'CgrCodes.active' => true,
            ])
            ->orderBy(['CgrCodes.type' => 'ASC', 'CgrCodes.code' => 'ASC'])
            ->all();

        // 3. Indexation des options par le TYPE nettoyé en Majuscules
        $options = [];
        foreach ($codes as $code) {
            $typeKey = strtoupper(trim((string)$code->type));
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