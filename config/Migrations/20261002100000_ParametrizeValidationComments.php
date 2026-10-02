<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Paramètre l'obligation de commentaire et supprime le stockage dupliqué des votes. */
final class ParametrizeValidationComments extends BaseMigration
{
    /** Ajoute les règles globales et supprime le commentaire dupliqué des étapes. */
    public function up(): void
    {
        $this->table('workflow_settings')->insert([
            [
                'name' => 'validation.comment_required.accept',
                'value' => '0',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'validation.comment_required.reject',
                'value' => '1',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
        ])->save();

        // Préserve les anciennes valeurs avant de supprimer la copie portée par l'étape.
        $this->execute("UPDATE validations v
            INNER JOIN applicationvalidationsteps s ON s.id = v.applicationvalidationstep_id
            SET v.obs = s.comment
            WHERE (v.obs IS NULL OR v.obs = '') AND s.comment IS NOT NULL");

        $this->table('applicationvalidationsteps')->removeColumn('comment')->update();
    }

    /** Restaure la colonne historique et supprime les paramètres ajoutés. */
    public function down(): void
    {
        $this->table('applicationvalidationsteps')
            ->addColumn('comment', 'string', ['limit' => 100, 'null' => true, 'default' => null])
            ->update();

        $this->execute("UPDATE applicationvalidationsteps s
            INNER JOIN validations v ON v.applicationvalidationstep_id = s.id
            SET s.comment = LEFT(v.obs, 100)
            WHERE v.obs IS NOT NULL");

        $this->execute("DELETE FROM workflow_settings
            WHERE name IN ('validation.comment_required.accept', 'validation.comment_required.reject')");
    }
}
