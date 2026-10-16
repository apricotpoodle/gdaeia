<?php
declare(strict_types=1);

namespace App\Service\Integrity;

use InvalidArgumentException;

/** Catalogue explicite des références relationnelles de l’application. */
final class ReferenceCatalog
{
    /** @return array<string, \App\Service\Integrity\ReferenceDefinition> */
    public function all(): array
    {
        // Les lignes longues représentent une définition atomique du catalogue.
        // phpcs:disable Generic.Files.LineLength
        $definitions = [
            new ReferenceDefinition('applicationforms.department_id', 'applicationforms', 'department_id', 'departments', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationforms.user_id', 'applicationforms', 'user_id', 'users', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationforms.contracttype_id', 'applicationforms', 'contracttype_id', 'contracttypes', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationforms.hiringreason_id', 'applicationforms', 'hiringreason_id', 'hiringreasons', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationforms.budgetfeature_id', 'applicationforms', 'budgetfeature_id', 'budgetfeatures', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationforms.professionalcategory_id', 'applicationforms', 'professionalcategory_id', 'professionalcategories', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationforms.worktime_id', 'applicationforms', 'worktime_id', 'worktimes', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationforms.period_id', 'applicationforms', 'period_id', 'periods', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationforms.yesno_id', 'applicationforms', 'yesno_id', 'yesnos', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationvalidationsteps.applicationform_id', 'applicationvalidationsteps', 'applicationform_id', 'applicationforms', nullable: false, targetPolicy: 'historical', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationvalidationsteps.role_id', 'applicationvalidationsteps', 'role_id', 'roles', nullable: false, targetPolicy: 'historical', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationvalidationsteps.validationstatus_id', 'applicationvalidationsteps', 'validationstatus_id', 'validationstatuses', nullable: true, targetPolicy: 'historical', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationvalidationsteps.validationsequence_id', 'applicationvalidationsteps', 'validationsequence_id', 'validationsequences', nullable: false, targetPolicy: 'historical', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('applicationvalidationsteps.validation_workflow_run_id', 'applicationvalidationsteps', 'validation_workflow_run_id', 'validation_workflow_runs', nullable: true, targetPolicy: 'historical', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('cgr_codes.department_id', 'cgr_codes', 'department_id', 'departments', nullable: false, targetPolicy: 'active'),
            new ReferenceDefinition('comments.parent_id', 'comments', 'parent_id', 'comments', nullable: true, targetPolicy: 'exists'),
            new ReferenceDefinition('comments.user_id', 'comments', 'user_id', 'users', nullable: false, targetPolicy: 'exists'),
            new ReferenceDefinition('departments.parent_id', 'departments', 'parent_id', 'departments', nullable: true, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('departments.cgr_code_id', 'departments', 'cgr_code_id', 'cgr_codes', nullable: true, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('departments.cgr_strategy_id', 'departments', 'cgr_strategy_id', 'cgr_strategies', nullable: true, targetPolicy: 'exists', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('departments.current_manager_id', 'departments', 'current_manager_id', 'users', nullable: true, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('email_recipients.email_log_id', 'email_recipients', 'email_log_id', 'email_logs', nullable: false, targetPolicy: 'exists'),
            new ReferenceDefinition('field_authorizations.role_id', 'field_authorizations', 'role_id', 'roles', nullable: false, targetPolicy: 'active'),
            new ReferenceDefinition('role_menus.role_id', 'role_menus', 'role_id', 'roles', nullable: false, targetPolicy: 'active'),
            new ReferenceDefinition('role_menus.menu_id', 'role_menus', 'menu_id', 'menus', nullable: false, targetPolicy: 'active'),
            new ReferenceDefinition('role_menus.department_id', 'role_menus', 'department_id', 'departments', nullable: true, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('user_departments.user_id', 'user_departments', 'user_id', 'users', nullable: false, targetPolicy: 'active'),
            new ReferenceDefinition('user_departments.department_id', 'user_departments', 'department_id', 'departments', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('users.role_id', 'users', 'role_id', 'roles', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('urds.user_id', 'urds', 'user_id', 'users', sourceKey: 'user_id', nullable: false, targetPolicy: 'active'),
            new ReferenceDefinition('urds.role_id', 'urds', 'role_id', 'roles', sourceKey: 'user_id', nullable: false, targetPolicy: 'active'),
            new ReferenceDefinition('urds.department_id', 'urds', 'department_id', 'departments', sourceKey: 'user_id', nullable: false, targetPolicy: 'active'),
            new ReferenceDefinition('validations.applicationform_id', 'validations', 'applicationform_id', 'applicationforms', nullable: false, targetPolicy: 'historical', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('validations.user_id', 'validations', 'user_id', 'users', nullable: false, targetPolicy: 'historical'),
            new ReferenceDefinition('validations.role_id', 'validations', 'role_id', 'roles', nullable: false, targetPolicy: 'historical'),
            new ReferenceDefinition('validations.validationstatus_id', 'validations', 'validationstatus_id', 'validationstatuses', nullable: true, targetPolicy: 'historical'),
            new ReferenceDefinition('validations.applicationvalidationstep_id', 'validations', 'applicationvalidationstep_id', 'applicationvalidationsteps', nullable: true, targetPolicy: 'historical'),
            new ReferenceDefinition('validationsequences.department_id', 'validationsequences', 'department_id', 'departments', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('validationsequences.role_id', 'validationsequences', 'role_id', 'roles', nullable: false, targetPolicy: 'active', sourceDeletedColumn: 'deleted'),
            new ReferenceDefinition('validation_workflow_runs.applicationform_id', 'validation_workflow_runs', 'applicationform_id', 'applicationforms', nullable: false, targetPolicy: 'historical'),
            new ReferenceDefinition('validation_workflow_runs.started_by_user_id', 'validation_workflow_runs', 'started_by_user_id', 'users', nullable: false, targetPolicy: 'historical'),
        ];
        // phpcs:enable Generic.Files.LineLength

        $indexed = [];
        foreach ($definitions as $definition) {
            $indexed[$definition->id] = $definition;
        }

        return $indexed;
    }

    /** Retourne une référence cataloguée ou lève une erreur explicite. */
    public function get(string $id): ReferenceDefinition
    {
        $definition = $this->all()[$id] ?? null;
        if ($definition === null) {
            throw new InvalidArgumentException(__('Référence inconnue : {0}.', $id));
        }

        return $definition;
    }
}
