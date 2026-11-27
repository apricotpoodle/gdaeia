<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Répare la vue de statut qui peut encore lire les validations historiques. */
final class RepairApplicationformStatusesFromWorkflowRuns extends BaseMigration
{
    /** Recrée le statut à partir de l'état du cycle immuable courant. */
    public function up(): void
    {
        $this->execute("CREATE OR REPLACE VIEW applicationformstatuses AS
            SELECT af.id AS applicationform_id,
              EXISTS(
                SELECT 1
                FROM applicationvalidationsteps s
                WHERE s.validation_workflow_run_id = wr.id
                  AND s.deleted IS NULL
                  AND s.state IN ('acceptee', 'refusee')
              ) AS has_validations,
              CASE wr.state
                WHEN 'en_attente' THEN 2
                WHEN 'acceptee' THEN 4
                WHEN 'refusee' THEN 5
                WHEN 'annulee' THEN 6
                ELSE 1
              END AS validationstatus_id,
              COALESCE((
                SELECT ROUND(100 * COUNT(*) / NULLIF((
                    SELECT COUNT(*)
                    FROM applicationvalidationsteps all_steps
                    WHERE all_steps.validation_workflow_run_id = wr.id
                      AND all_steps.deleted IS NULL
                ), 0), 2)
                FROM applicationvalidationsteps completed_steps
                WHERE completed_steps.validation_workflow_run_id = wr.id
                  AND completed_steps.deleted IS NULL
                  AND completed_steps.state IN ('acceptee', 'refusee')
              ), 0) AS valid_percentage,
              (
                SELECT MIN(waiting_steps.sequence_number)
                FROM applicationvalidationsteps waiting_steps
                WHERE waiting_steps.validation_workflow_run_id = wr.id
                  AND waiting_steps.deleted IS NULL
                  AND waiting_steps.state = 'en_attente'
              ) AS current_sequence,
              wr.state = 'en_attente' AS en_cours,
              wr.state = 'acceptee' AS accepted,
              wr.state = 'refusee' AS rejected
            FROM applicationforms af
            LEFT JOIN validation_workflow_runs wr ON wr.applicationform_id = af.id");
    }

    /** Le retour à une vue historique réintroduirait le défaut corrigé. */
    public function down(): void
    {
        // La réparation est conservée en cas de rollback applicatif.
    }
}
