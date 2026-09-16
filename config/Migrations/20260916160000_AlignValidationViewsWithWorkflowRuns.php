<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Aligne les vues de suivi sur l'instantané immuable d'un cycle. */
final class AlignValidationViewsWithWorkflowRuns extends BaseMigration
{
    public function up(): void
    {
        $this->execute("CREATE OR REPLACE VIEW applicationformstatuses AS
            SELECT af.id AS applicationform_id,
              EXISTS(SELECT 1 FROM applicationvalidationsteps s WHERE s.validation_workflow_run_id = wr.id AND s.state IN ('acceptee', 'refusee')) AS has_validations,
              CASE wr.state WHEN 'en_attente' THEN 2 WHEN 'acceptee' THEN 4 WHEN 'refusee' THEN 5 WHEN 'annulee' THEN 6 ELSE 1 END AS validationstatus_id,
              COALESCE((SELECT ROUND(100 * COUNT(*) / NULLIF((SELECT COUNT(*) FROM applicationvalidationsteps all_steps WHERE all_steps.validation_workflow_run_id = wr.id), 0), 2) FROM applicationvalidationsteps completed_steps WHERE completed_steps.validation_workflow_run_id = wr.id AND completed_steps.state IN ('acceptee', 'refusee')), 0) AS valid_percentage,
              (SELECT MIN(waiting_steps.sequence_number) FROM applicationvalidationsteps waiting_steps WHERE waiting_steps.validation_workflow_run_id = wr.id AND waiting_steps.state = 'en_attente') AS current_sequence,
              wr.state = 'en_attente' AS en_cours,
              wr.state = 'acceptee' AS accepted,
              wr.state = 'refusee' AS rejected
            FROM applicationforms af
            LEFT JOIN validation_workflow_runs wr ON wr.applicationform_id = af.id");

        $this->execute("CREATE OR REPLACE VIEW currentvalidationroles AS
            SELECT af.id AS applicationform_id, af.department_id, s.role_id AS validator_role_id,
              s.sequence_number AS validation_sequence, 2 AS validationstatus_id,
              wr.state = 'en_attente' AS en_cours, wr.state = 'acceptee' AS accepted,
              wr.state = 'refusee' AS rejected
            FROM validation_workflow_runs wr
            JOIN applicationforms af ON af.id = wr.applicationform_id
            JOIN applicationvalidationsteps s ON s.validation_workflow_run_id = wr.id
            WHERE s.state = 'en_attente'");

        $this->execute("CREATE OR REPLACE VIEW validation_visas AS
            SELECT s.applicationform_id, s.sequence_number AS sequence, s.role_id,
              CONCAT(u.firstname, ' ', u.lastname) AS op_name, r.name AS role_name,
              status.name AS status_name, v.validated AS validated_at
            FROM validations v
            JOIN applicationvalidationsteps s ON s.id = v.applicationvalidationstep_id
            JOIN roles r ON r.id = s.role_id
            LEFT JOIN users u ON u.id = v.user_id
            LEFT JOIN validationstatuses status ON status.id = v.validationstatus_id
            WHERE v.deleted IS NULL
            ORDER BY s.applicationform_id, s.sequence_number, s.role_id");
    }

    public function down(): void
    {
        // La migration fondatrice recrée les vues historiques lors de son rollback.
    }
}
