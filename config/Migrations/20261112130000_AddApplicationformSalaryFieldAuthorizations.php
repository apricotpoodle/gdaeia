<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Déclare et autorise les champs de calcul de rémunération des DAE. */
final class AddApplicationformSalaryFieldAuthorizations extends BaseMigration
{
    /** Ajoute les champs tardifs au catalogue et à chaque matrice de rôle. */
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->execute("INSERT IGNORE INTO field_definitions
            (resource, field, label, description, active, position, created, modified)
            VALUES
                ('Applicationforms', 'working_days', 'Nombre de jours ouvrés',
                 'Nombre de jours utilisés pour calculer une rémunération journalière.',
                 1, 100, '{$now}', '{$now}'),
                ('Applicationforms', 'salary_base', 'Salaire de base calculé',
                 'Montant de base calculé à partir de la rémunération et de sa périodicité.',
                 1, 101, '{$now}', '{$now}')");

        $this->execute("INSERT IGNORE INTO field_authorizations
            (role_id, resource, target_type, target, access_level)
            SELECT id, 'Applicationforms', 'field', 'working_days', 'EDIT'
            FROM roles");
        $this->execute("INSERT IGNORE INTO field_authorizations
            (role_id, resource, target_type, target, access_level)
            SELECT id, 'Applicationforms', 'field', 'salary_base', 'VIEW'
            FROM roles");
    }

    /** Les règles administrables restent en place après un retour arrière. */
    public function down(): void
    {
    }
}
