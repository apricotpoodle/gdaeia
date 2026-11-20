<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Déclare les champs séparés du nom du candidat et leurs droits ACL. */
final class AddApplicationformApplicantNameFieldAuthorizations extends BaseMigration
{
    /** Ajoute les champs au catalogue et reprend les droits de applicantname. */
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->execute("INSERT IGNORE INTO field_definitions
            (resource, field, label, description, active, position, created, modified)
            VALUES
                ('Applicationforms', 'applicant_first_name', 'Prénom du candidat',
                 'Prénom du candidat externe.', 1, 102, '{$now}', '{$now}'),
                ('Applicationforms', 'applicant_last_name', 'Nom du candidat',
                 'Nom du candidat externe.', 1, 103, '{$now}', '{$now}')");

        $this->execute("INSERT IGNORE INTO field_authorizations
            (role_id, resource, target_type, target, access_level)
            SELECT roles.id, 'Applicationforms', 'field', field_targets.target,
                   COALESCE(applicantname.access_level, 'NONE')
            FROM roles
            CROSS JOIN (
                SELECT 'applicant_first_name' AS target
                UNION ALL SELECT 'applicant_last_name'
            ) field_targets
            LEFT JOIN field_authorizations applicantname
                ON applicantname.role_id = roles.id
                AND applicantname.resource = 'Applicationforms'
                AND applicantname.target_type = 'field'
                AND applicantname.target = 'applicantname'");
    }

    /** Les droits restent administrables et ne sont pas supprimés au rollback. */
    public function down(): void
    {
    }
}
