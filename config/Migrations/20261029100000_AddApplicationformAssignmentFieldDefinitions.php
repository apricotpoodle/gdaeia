<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Déclare les libellés des champs d'affectation des demandes de recrutement. */
final class AddApplicationformAssignmentFieldDefinitions extends BaseMigration
{
    /** Ajoute les métadonnées utilisées par les messages de validation. */
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $definitions = [
            [
                'resource' => 'Applicationforms',
                'field' => 'assistant_id',
                'label' => 'Assistante',
                'description' => 'Utilisateur affecté comme assistant ou assistante de la demande.',
            ],
            [
                'resource' => 'Applicationforms',
                'field' => 'manager_id',
                'label' => 'Chef de service',
                'description' => 'Utilisateur affecté comme responsable de la demande.',
            ],
        ];

        foreach ($definitions as $position => $definition) {
            $this->table('field_definitions')->insert([
                $definition + [
                    'active' => true,
                    'position' => $position,
                    'created' => $now,
                    'modified' => $now,
                ],
            ])->save();
        }
    }

    /** Les métadonnées restent conservées pour ne pas perdre une personnalisation. */
    public function down(): void
    {
    }
}
