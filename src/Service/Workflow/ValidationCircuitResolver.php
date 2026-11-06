<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Validationsequence;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/** Résout le même parcours actif pour l'administration et le lancement d'une DAE. */
final class ValidationCircuitResolver
{
    private Table $sequences;
    private Table $rules;
    private Table $roles;
    private Table $departments;
    private Table $contracttypes;

    /** Initialise les référentiels nécessaires à la résolution locale. */
    public function __construct()
    {
        $locator = TableRegistry::getTableLocator();
        $this->sequences = $locator->get('Validationsequences');
        $this->rules = $locator->get('ValidationCircuitRules');
        $this->roles = $locator->get('Roles');
        $this->departments = $locator->get('Departments');
        $this->contracttypes = $locator->get('Contracttypes');
    }

    /**
     * Retient les séquences jusqu'au rôle final, y compris ses rôles parallèles.
     *
     * Sans rôle explicite, seule la règle du couple exact département–contrat
     * s'applique. Sans DAE, l'éligibilité du demandeur sera vérifiée au lancement.
     *
     * @param int $departmentId Département de la DAE ou de la configuration.
     * @param int $contracttypeId Type de contrat de la DAE ou de la configuration.
     * @param int|null $finalRoleId Rôle final proposé ; sinon règle persistée.
     * @param \App\Model\Entity\Applicationform|null $applicationform DAE à lancer, si connue.
     * @return array{sequences: list<\App\Model\Entity\Validationsequence>, issues: list<string>}
     */
    public function resolve(
        int $departmentId,
        int $contracttypeId,
        ?int $finalRoleId = null,
        ?Applicationform $applicationform = null,
    ): array {
        if ($finalRoleId === null && $contracttypeId > 0) {
            $rule = $this->rules->find()->where([
                'department_id' => $departmentId,
                'contracttype_id' => $contracttypeId,
            ])->first();
            $finalRoleId = $rule === null ? null : (int)$rule->get('final_role_id');
        }

        /** @var list<\App\Model\Entity\Validationsequence> $sequences */
        $sequences = $this->sequences->find()->contain(['Roles'])->where([
            'Validationsequences.department_id' => $departmentId,
            'Validationsequences.deleted IS' => null,
        ])->orderByAsc('Validationsequences.sequence')->all()->toList();

        if ($finalRoleId !== null) {
            $finalSequence = null;
            foreach ($sequences as $sequence) {
                if ((int)$sequence->role_id === $finalRoleId && $sequence->role->deleted === null) {
                    $finalSequence = (int)$sequence->sequence;
                    break;
                }
            }
            if ($finalSequence === null) {
                return ['sequences' => [], 'issues' => [__(
                    'Le circuit du contrat « {0} » dans le département « {1} » demande le rôle final « {2} », '
                    . 'absent ou inactif dans ce circuit.',
                    $this->referenceName($this->contracttypes, $contracttypeId),
                    $this->referenceName($this->departments, $departmentId),
                    $this->referenceName($this->roles, $finalRoleId),
                )]];
            }
            $sequences = array_values(array_filter(
                $sequences,
                static fn(Validationsequence $sequence): bool => (int)$sequence->sequence <= $finalSequence,
            ));
        }
        if ($sequences === []) {
            return ['sequences' => [], 'issues' => [__(
                'Aucune séquence de validation n’est configurée pour ce département.',
            )]];
        }

        $workflow = new ApplicationformValidationWorkflow();
        $candidate = $applicationform ?? new Applicationform(['department_id' => $departmentId]);
        $issues = [];
        foreach ($sequences as $sequence) {
            $roleId = (int)$sequence->role_id;
            if ($sequence->role->deleted !== null) {
                $issues[] = __(
                    'Le rôle « {0} » de la séquence {1} du contrat « {2} » dans le département « {3} » est inactif.',
                    $sequence->role->name,
                    $sequence->sequence,
                    $this->referenceName($this->contracttypes, $contracttypeId),
                    $this->referenceName($this->departments, $departmentId),
                );

                continue;
            }
            if ($applicationform === null && $workflow->isRequesterRole($roleId)) {
                continue;
            }
            if ($workflow->eligibleUsers($candidate, $roleId) === []) {
                $issues[] = __(
                    'Le rôle « {0} » de la séquence {1} du contrat « {2} » dans le département « {3} » '
                    . 'ne possède aucun validateur éligible.',
                    $sequence->role->name,
                    $sequence->sequence,
                    $this->referenceName($this->contracttypes, $contracttypeId),
                    $this->referenceName($this->departments, $departmentId),
                );
            }
        }

        return ['sequences' => $sequences, 'issues' => $issues];
    }

    /** Retourne un libellé exploitable même si la référence est devenue introuvable. */
    private function referenceName(Table $table, int $id): string
    {
        $reference = $table->find()->select(['name'])->where(['id' => $id])->first();

        return $reference === null ? __('n°{0}', $id) : (string)$reference->get('name');
    }
}
