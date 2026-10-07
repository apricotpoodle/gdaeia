<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Ajoute le dictionnaire centralisé des champs métier. */
final class CreateFieldDefinitions extends BaseMigration
{
    /** Crée le référentiel et charge les libellés initiaux. */
    public function up(): void
    {
        $this->table('field_definitions')
            ->addColumn('resource', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('field', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('label', 'string', ['limit' => 160, 'null' => false])
            ->addColumn('description', 'text', ['null' => true, 'default' => null])
            ->addColumn('active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('position', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['resource', 'field'], ['unique' => true, 'name' => 'uq_field_definitions_resource_field'])
            ->addIndex(['resource', 'active', 'position'], ['name' => 'idx_field_definitions_listing'])
            ->create();

        $now = date('Y-m-d H:i:s');
        $definitions = [
            ['Applicationforms', 'jobtitle', 'Intitulé du poste', 'Poste concerné par la demande.'],
            ['Applicationforms', 'department_id', 'Département', 'Département de rattachement de la demande.'],
            ['Applicationforms', 'applicantname', 'Nom du candidat', 'Nom du candidat externe saisi librement.'],
            ['Applicationforms', 'candidate_name', 'Candidat / collaborateur', 'Nom affiché du candidat ou du collaborateur pressenti.'],
            ['Applicationforms', 'collaborator_id', 'Collaborateur interne pressenti', 'Collaborateur interne éventuellement associé à la demande.'],
            ['Applicationforms', 'contracttype_id', 'Type de contrat', 'Type de contrat demandé.'],
            ['Applicationforms', 'hiringreason_id', 'Motif de recrutement', 'Motif principal de la demande.'],
            ['Applicationforms', 'reasonforreplacement', 'Précisions du motif', 'Précisions ou personne remplacée.'],
            ['Applicationforms', 'professionalcategory_id', 'Catégorie professionnelle', 'Catégorie professionnelle du poste.'],
            ['Applicationforms', 'worktime_id', 'Temps de travail', 'Modalité de temps de travail.'],
            ['Applicationforms', 'workingtimedistribution', 'Répartition du temps de travail', 'Répartition détaillée du temps de travail.'],
            ['Applicationforms', 'cgr', 'Code CGR', 'Code analytique ou budgétaire de la demande.'],
            ['Applicationforms', 'budgetfeature_id', 'Caractéristique budgétaire', 'Caractéristique d’imputation budgétaire.'],
            ['Applicationforms', 'grossremuneration', 'Rémunération brute', 'Montant de la rémunération brute.'],
            ['Applicationforms', 'period_id', 'Périodicité', 'Périodicité associée à la rémunération.'],
            ['Applicationforms', 'qualification', 'Qualification retenue', 'Qualification attendue pour le poste.'],
            ['Applicationforms', 'begin_at', 'Date de début', 'Date prévue de début du contrat.'],
            ['Applicationforms', 'end_at', 'Date de fin', 'Date prévue de fin du contrat.'],
            ['Applicationforms', 'yesno_id', 'Inscrit au budget', 'Indique si le poste est inscrit au budget.'],
            ['Users', 'user_id', 'Utilisateur', null],
            ['Users', 'email', 'Adresse courriel', null],
            ['Users', 'username', "Nom d'utilisateur", null],
            ['Users', 'password', 'Mot de passe', null],
            ['Users', 'role_id', 'Rôle applicatif', null],
            ['Users', 'user_departments', 'Périmètre organisationnel', null],
            ['Users', 'department_id', 'Département', null],
            ['Users', 'firstname', 'Prénom', null],
            ['Users', 'lastname', 'Nom', null],
            ['Roles', 'code', 'Code', null],
            ['Roles', 'name', 'Libellé', null],
            ['Roles', 'sort', 'Clé de tri', null],
            ['Roles', 'base', 'Rôle socle', null],
            ['Roles', 'deleted', 'Date de désactivation', null],
            ['Menus', 'parent_id', 'Menu parent', null],
            ['Menus', 'name', 'Nom du menu', null],
            ['Menus', 'url', 'URL du menu', null],
            ['Menus', 'active', 'Actif', null],
            ['Menus', 'disabled', 'Désactivé', null],
            ['Menus', 'dividor_before', 'Séparateur avant', null],
            ['Menus', 'level', 'Niveau', null],
            ['Comments', 'parent_id', 'Commentaire parent', null],
            ['Comments', 'model', 'Type de ressource', null],
            ['Comments', 'foreign_key', 'Élément commenté', null],
            ['Comments', 'type', 'Type de commentaire', null],
            ['Comments', 'content', 'Contenu', null],
            ['Comments', 'user_id', 'Auteur', null],
            ['FieldAuthorizations', 'role_id', 'Rôle applicatif', null],
            ['FieldAuthorizations', 'resource', 'Ressource', null],
            ['FieldAuthorizations', 'field', 'Champ', null],
            ['FieldAuthorizations', 'access_level', "Niveau d'accès", null],
            ['ValidationCommentTemplates', 'decision', 'Décision', null],
            ['ValidationCommentTemplates', 'label', 'Libellé', null],
            ['ValidationCommentTemplates', 'content', 'Contenu', null],
            ['ValidationCommentTemplates', 'position', 'Position', null],
            ['ValidationCommentTemplates', 'active', 'Actif', null],
            ['WorkflowSettings', 'name', 'Paramètre', null],
            ['WorkflowSettings', 'value', 'Valeur', null],
            ['RoleMenus', 'role_id', 'Rôle applicatif', null],
            ['RoleMenus', 'menu_id', 'Menu', null],
            ['RoleMenus', 'department_id', 'Département', null],
            ['Validationsequences', 'department_id', 'Département', null],
            ['Validationsequences', 'role_id', 'Rôle validateur', null],
            ['Validationsequences', 'sequence', 'Numéro de séquence', null],
            ['Validationsequences', 'name', 'Nom de la séquence', null],
            ['Validationsequences', 'description', 'Description', null],
            ['Validationsequences', 'reminder_delay_hours', 'Délai de relance', null],
            ['Validationsequences', 'deleted', 'Date de désactivation', null],
        ];

        $rows = [];
        foreach ($definitions as $position => [$resource, $field, $label, $description]) {
            $rows[] = compact('resource', 'field', 'label', 'description') + [
                'active' => true,
                'position' => $position,
                'created' => $now,
                'modified' => $now,
            ];
        }
        $this->table('field_definitions')->insert($rows)->save();
    }

    /** Supprime le référentiel. */
    public function down(): void
    {
        $this->table('field_definitions')->drop()->save();
    }
}
