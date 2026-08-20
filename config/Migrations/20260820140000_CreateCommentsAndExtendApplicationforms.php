<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Migration : Creation de la table centralisee 'comments' et extension d'applicationforms.
 */
class CreateCommentsAndExtendApplicationforms extends BaseMigration
{
    /**
     * Method Up : Applique les modifications sur la base de donnees.
     *
     * @return void
     */
    public function up(): void
    {
        // 1. Creation de la table centrale polymorphique des commentaires
        $comments = $this->table('comments');
        $comments
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'null' => true,
                'signed' => false,
                'comment' => 'ID du commentaire parent (0 ou NULL si premier niveau)',
            ])
            ->addColumn('model', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'Nom du modele associe (ex: Applicationforms)',
            ])
            ->addColumn('foreign_key', 'integer', [
                'null' => false,
                'signed' => false,
                'comment' => 'ID de l enregistrement lie dans le modele',
            ])
            ->addColumn('type', 'string', [
                'default' => 'GENERAL',
                'limit' => 32,
                'null' => false,
                'comment' => 'Type/Contexte (OBSERVATION, HIRING_REASON, PART_TIME, GENERAL)',
            ])
            ->addColumn('content', 'text', [
                'null' => false,
                'comment' => 'Contenu texte du commentaire',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'signed' => false,
                'comment' => 'ID de l auteur du commentaire',
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
                'comment' => 'Horodatage de creation',
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'null' => true,
                'comment' => 'Horodatage de derniere modification',
            ])
            ->addIndex(['model', 'foreign_key'], [
                'name' => 'idx_comments_polymorphic',
            ])
            ->addIndex(['parent_id'], [
                'name' => 'idx_comments_parent',
            ])
            ->addIndex(['user_id'], [
                'name' => 'idx_comments_user',
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'fk_comments_user',
            ])
            ->create();

        // 2. Extension de la table applicationforms
        $appForms = $this->table('applicationforms');
        $appForms
            ->addColumn('collaborator_id', 'biginteger', [
                'default' => null,
                'limit' => 11,
                'null' => true,
                'comment' => 'ID collaborateur interne concerne (CLB_ID)',
            ])
            ->addColumn('archived', 'datetime', [
                'default' => null,
                'null' => true,
                'comment' => 'Horodatage d archivage de la fiche (DAE_ARCHIVE_IND)',
            ])
            ->update();
    }

    /**
     * Method Down : Annule proprement les modifications.
     *
     * @return void
     */
    public function down(): void
    {
        // 1. Suppression de la table comments
        if ($this->hasTable('comments')) {
            $this->table('comments')->drop()->save();
        }

        // 2. Retrait des colonnes ajoutees dans applicationforms
        $appForms = $this->table('applicationforms');
        if ($appForms->hasColumn('collaborator_id')) {
            $appForms->removeColumn('collaborator_id');
        }
        if ($appForms->hasColumn('archived')) {
            $appForms->removeColumn('archived');
        }
        $appForms->update();
    }
}