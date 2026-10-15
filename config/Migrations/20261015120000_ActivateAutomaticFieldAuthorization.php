<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Active l'ACL des champs pour les mutations Web et API. */
final class ActivateAutomaticFieldAuthorization extends BaseMigration
{
    /**
     * Complète le catalogue métier et initialise les matrices des rôles.
     *
     * Les droits EDIT initiaux conservent le comportement historique pour les
     * champs déjà modifiables. Les restrictions sensibles sont ensuite posées
     * explicitement, notamment pour le champ base des départements.
     */
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $definitions = [
            ['resource' => 'CgrCodes', 'field' => 'type', 'label' => 'Type', 'description' => 'Type métier du code CGR.'],
            ['resource' => 'CgrCodes', 'field' => 'code', 'label' => 'Code', 'description' => 'Valeur du code CGR.'],
            ['resource' => 'CgrCodes', 'field' => 'label', 'label' => 'Libellé', 'description' => 'Libellé du code CGR.'],
            ['resource' => 'CgrCodes', 'field' => 'active', 'label' => 'Actif', 'description' => 'État d’activation du code CGR.'],
            ['resource' => 'Contracttypes', 'field' => 'code', 'label' => 'Code', 'description' => 'Code du type de contrat.'],
            ['resource' => 'Contracttypes', 'field' => 'name', 'label' => 'Nom', 'description' => 'Libellé du type de contrat.'],
            ['resource' => 'Hiringreasons', 'field' => 'code', 'label' => 'Code', 'description' => 'Code du motif de recrutement.'],
            ['resource' => 'Hiringreasons', 'field' => 'name', 'label' => 'Nom', 'description' => 'Libellé du motif de recrutement.'],
            ['resource' => 'Professionalcategories', 'field' => 'code', 'label' => 'Code', 'description' => 'Code de la catégorie professionnelle.'],
            ['resource' => 'Professionalcategories', 'field' => 'name', 'label' => 'Nom', 'description' => 'Libellé de la catégorie professionnelle.'],
            ['resource' => 'Worktimes', 'field' => 'code', 'label' => 'Code', 'description' => 'Code du temps de travail.'],
            ['resource' => 'Worktimes', 'field' => 'name', 'label' => 'Nom', 'description' => 'Libellé du temps de travail.'],
            ['resource' => 'Periods', 'field' => 'code', 'label' => 'Code', 'description' => 'Code de la période.'],
            ['resource' => 'Periods', 'field' => 'name', 'label' => 'Nom', 'description' => 'Libellé de la période.'],
            ['resource' => 'Budgetfeatures', 'field' => 'code', 'label' => 'Code', 'description' => 'Code de la caractéristique budgétaire.'],
            ['resource' => 'Budgetfeatures', 'field' => 'name', 'label' => 'Nom', 'description' => 'Libellé de la caractéristique budgétaire.'],
            ['resource' => 'Yesnos', 'field' => 'code', 'label' => 'Code', 'description' => 'Code de la valeur oui/non.'],
            ['resource' => 'Yesnos', 'field' => 'name', 'label' => 'Nom', 'description' => 'Libellé de la valeur oui/non.'],
            ['resource' => 'WorkflowSettings', 'field' => 'default_due_hours', 'label' => 'Délai par défaut', 'description' => 'Délai global de validation en heures.'],
            ['resource' => 'WorkflowSettings', 'field' => 'blocked_after_business_days', 'label' => 'Délai de blocage', 'description' => 'Délai de détection des cycles bloqués.'],
            ['resource' => 'WorkflowSettings', 'field' => 'accepter', 'label' => 'Commentaire à l’acceptation', 'description' => 'Obligation de commentaire lors de l’acceptation.'],
            ['resource' => 'WorkflowSettings', 'field' => 'refuser', 'label' => 'Commentaire au refus', 'description' => 'Obligation de commentaire lors du refus.'],
            ['resource' => 'Validationsequences', 'field' => 'department_ids', 'label' => 'Départements sélectionnés', 'description' => 'Départements ciblés par la mutation de séquence.'],
            ['resource' => 'RoleMenus', 'field' => 'menu_ids', 'label' => 'Menus sélectionnés', 'description' => 'Options de menu ciblées par l’association.'],
            ['resource' => 'Departments', 'field' => 'parent_id', 'label' => 'Département parent', 'description' => 'Département parent de la branche.'],
            ['resource' => 'Departments', 'field' => 'cgr_code_id', 'label' => 'CGR par défaut', 'description' => 'Code CGR utilisé par défaut par le département.'],
            ['resource' => 'Departments', 'field' => 'code', 'label' => 'Code', 'description' => 'Code métier du département.'],
            ['resource' => 'Departments', 'field' => 'name', 'label' => 'Nom', 'description' => 'Nom affiché du département.'],
            ['resource' => 'Departments', 'field' => 'sort', 'label' => 'Clé de tri', 'description' => 'Clé utilisée pour ordonner les départements.'],
            ['resource' => 'Departments', 'field' => 'department_type_id', 'label' => 'Type de département', 'description' => 'Type métier du département.'],
            ['resource' => 'Departments', 'field' => 'cgr_strategy_id', 'label' => 'Stratégie CGR', 'description' => 'Stratégie de rattachement des codes CGR.'],
            ['resource' => 'Departments', 'field' => 'current_manager_id', 'label' => 'Responsable', 'description' => 'Utilisateur responsable du département.'],
            ['resource' => 'Departments', 'field' => 'base', 'label' => 'Département socle', 'description' => 'Indique qu’un département constitue un socle protégé.'],
        ];

        $rows = [];
        foreach ($definitions as $position => $definition) {
            $rows[] = $definition + [
                'active' => true,
                'position' => $position,
                'created' => $now,
                'modified' => $now,
            ];
        }

        $this->table('field_definitions')->insert($rows)->save();

        $this->execute("INSERT INTO field_authorizations (role_id, resource, field, access_level)
            SELECT roles.id, definitions.resource, definitions.field, 'EDIT'
            FROM roles
            CROSS JOIN field_definitions definitions
            LEFT JOIN field_authorizations authorizations
                ON authorizations.role_id = roles.id
                AND authorizations.resource = definitions.resource
                AND authorizations.field = definitions.field
            WHERE definitions.active = 1
                AND authorizations.id IS NULL");

        $this->execute("UPDATE field_authorizations authorizations
            INNER JOIN roles
                ON roles.id = authorizations.role_id
            SET authorizations.access_level = 'NONE'
            WHERE authorizations.resource = 'Departments'
                AND authorizations.field = 'base'
                AND roles.base = 0");
    }

    /** La migration de données n'est pas réversible sans perdre les réglages administrés. */
    public function down(): void
    {
    }
}
