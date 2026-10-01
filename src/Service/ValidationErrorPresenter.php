<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\EntityInterface;

/** Présente les erreurs de validation ORM sans dépendre du canal Web ou API. */
final class ValidationErrorPresenter
{
    /** @var array<string, array<string, string>> */
    private const FIELD_LABELS = [
        'Applicationforms' => [
            'department_id' => 'Département',
            'user_id' => 'Créateur de la demande',
            'cgr' => 'Code CGR',
            'contracttype_id' => 'Type de contrat',
            'hiringreason_id' => 'Motif de recrutement',
            'reasonforreplacement' => 'Précision du motif',
            'budgetfeature_id' => 'Imputation budgétaire',
            'jobtitle' => 'Intitulé du poste',
            'professionalcategory_id' => 'Catégorie professionnelle',
            'worktime_id' => 'Temps de travail',
            'workingtimedistribution' => 'Répartition du temps de travail',
            'grossremuneration' => 'Rémunération brute',
            'period_id' => 'Périodicité',
            'qualification' => 'Qualification',
            'begin_at' => 'Date de début',
            'end_at' => 'Date de fin',
            'applicantname' => 'Nom du candidat',
            'yesno_id' => 'Champ Oui/Non',
        ],
        'Users' => [
            'user_id' => 'Utilisateur',
            'email' => 'Adresse courriel',
            'username' => "Nom d'utilisateur",
            'password' => 'Mot de passe',
            'role_id' => 'Rôle applicatif',
            'user_departments' => 'Périmètre organisationnel',
            'department_id' => 'Département',
            'firstname' => 'Prénom',
            'lastname' => 'Nom',
        ],
        'Roles' => [
            'code' => 'Code',
            'name' => 'Libellé',
            'sort' => 'Clé de tri',
            'base' => 'Rôle socle',
            'deleted' => 'Date de désactivation',
        ],
        'Menus' => [
            'parent_id' => 'Menu parent',
            'name' => 'Nom du menu',
            'url' => 'URL du menu',
            'active' => 'Actif',
            'disabled' => 'Désactivé',
            'dividor_before' => 'Séparateur avant',
            'level' => 'Niveau',
        ],
        'Comments' => [
            'parent_id' => 'Commentaire parent',
            'model' => 'Type de ressource',
            'foreign_key' => 'Élément commenté',
            'type' => 'Type de commentaire',
            'content' => 'Contenu',
            'user_id' => 'Auteur',
        ],
        'FieldAuthorizations' => [
            'role_id' => 'Rôle applicatif',
            'resource' => 'Ressource',
            'field' => 'Champ',
            'access_level' => "Niveau d'accès",
        ],
        'ValidationCommentTemplates' => [
            'decision' => 'Décision',
            'label' => 'Libellé',
            'content' => 'Contenu',
            'position' => 'Position',
            'active' => 'Actif',
        ],
        'WorkflowSettings' => [
            'name' => 'Paramètre',
            'value' => 'Valeur',
        ],
        'RoleMenus' => [
            'role_id' => 'Rôle applicatif',
            'menu_id' => 'Menu',
            'department_id' => 'Département',
        ],
        'Validationsequences' => [
            'department_id' => 'Département',
            'role_id' => 'Rôle validateur',
            'sequence' => 'Numéro de séquence',
            'name' => 'Nom de la séquence',
            'description' => 'Description',
            'reminder_delay_hours' => 'Délai de relance',
            'deleted' => 'Date de désactivation',
        ],
    ];

    /**
     * @return array{summary: string, errors: list<array{field: string, label: string, reason: string}>}
     */
    public function present(EntityInterface $entity, string $resource): array
    {
        $errors = [];
        $this->collect($entity->getErrors(), [], false, $resource, $errors);

        $summary = $errors === []
            ? __('Aucun détail de validation n’a été retourné.')
            : __('Champ « {0} » : {1}', $errors[0]['label'], $errors[0]['reason']);

        return ['summary' => $summary, 'errors' => $errors];
    }

    /**
     * @param array<array-key, mixed> $nodes Erreurs d'une entité ou d'un champ.
     * @param list<string> $path Chemin de l'association ou du champ courant.
     * @param bool $isField Indique si les clés de chaînes sont des règles de validation.
     * @param string $resource Ressource utilisée pour résoudre les libellés.
     * @param list<array{field: string, label: string, reason: string}> $results Erreurs présentées.
     */
    private function collect(array $nodes, array $path, bool $isField, string $resource, array &$results): void
    {
        foreach ($nodes as $key => $value) {
            if ($isField && is_string($value)) {
                $this->append($path, $value, $resource, $results);
                continue;
            }

            $nextPath = [...$path, (string)$key];
            if (is_string($value)) {
                $this->append($nextPath, $value, $resource, $results);
            } elseif (is_array($value)) {
                $this->collect($value, $nextPath, !is_int($key), $resource, $results);
            }
        }
    }

    /**
     * @param list<string> $path Chemin technique du champ.
     * @param list<array{field: string, label: string, reason: string}> $results Erreurs présentées.
     */
    private function append(array $path, string $reason, string $resource, array &$results): void
    {
        $field = implode('.', $path);
        $name = (string)end($path);
        $label = self::FIELD_LABELS[$resource][$name] ?? __('Champ non répertorié ({0})', $name);

        $results[] = [
            'field' => $field,
            'label' => __($label),
            'reason' => $reason,
        ];
    }
}
