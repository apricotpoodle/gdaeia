<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Définitions minimales utilisées par les tests de présentation des erreurs. */
final class FieldDefinitionsFixture extends TestFixture
{
    public string $table = 'field_definitions';

    /** @inheritDoc */
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'resource' => 'Applicationforms', 'field' => 'jobtitle', 'label' => 'Intitulé du poste', 'description' => null, 'active' => 1, 'position' => 1],
            ['id' => 2, 'resource' => 'Applicationforms', 'field' => 'department_id', 'label' => 'Département', 'description' => null, 'active' => 1, 'position' => 2],
            ['id' => 3, 'resource' => 'Users', 'field' => 'email', 'label' => 'Adresse courriel', 'description' => null, 'active' => 1, 'position' => 3],
            ['id' => 4, 'resource' => 'Users', 'field' => 'password', 'label' => 'Mot de passe', 'description' => null, 'active' => 1, 'position' => 4],
            ['id' => 5, 'resource' => 'Users', 'field' => 'user_departments', 'label' => 'Périmètre organisationnel', 'description' => null, 'active' => 1, 'position' => 5],
            ['id' => 6, 'resource' => 'Roles', 'field' => 'code', 'label' => 'Code', 'description' => null, 'active' => 1, 'position' => 6],
            ['id' => 7, 'resource' => 'Roles', 'field' => 'name', 'label' => 'Libellé', 'description' => null, 'active' => 1, 'position' => 7],
            ['id' => 8, 'resource' => 'Roles', 'field' => 'sort', 'label' => 'Clé de tri', 'description' => null, 'active' => 1, 'position' => 8],
            ['id' => 9, 'resource' => 'Menus', 'field' => 'name', 'label' => 'Nom du menu', 'description' => null, 'active' => 1, 'position' => 9],
            ['id' => 10, 'resource' => 'Comments', 'field' => 'content', 'label' => 'Contenu', 'description' => null, 'active' => 1, 'position' => 10],
            ['id' => 11, 'resource' => 'FieldAuthorizations', 'field' => 'resource', 'label' => 'Ressource', 'description' => null, 'active' => 1, 'position' => 11],
            ['id' => 12, 'resource' => 'WorkflowSettings', 'field' => 'name', 'label' => 'Paramètre', 'description' => null, 'active' => 1, 'position' => 12],
            ['id' => 13, 'resource' => 'ValidationCommentTemplates', 'field' => 'label', 'label' => 'Libellé', 'description' => null, 'active' => 1, 'position' => 13],
            ['id' => 14, 'resource' => 'ValidationCommentTemplates', 'field' => 'decision', 'label' => 'Décision', 'description' => null, 'active' => 1, 'position' => 14],
        ];
        $this->records = array_map(
            static fn(array $record): array => $record + [
                'created' => '2026-10-07 00:00:00',
                'modified' => '2026-10-07 00:00:00',
            ],
            $this->records,
        );
        parent::init();
    }
}
