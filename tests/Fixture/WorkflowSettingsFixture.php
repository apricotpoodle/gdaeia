<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Jeu de données isolé pour les paramètres globaux du workflow. */
class WorkflowSettingsFixture extends TestFixture
{
    /**
     * @var string
     */
    public string $table = 'workflow_settings';

    public function init(): void
    {
        $this->records = [[
            'id' => 1,
            'name' => 'validation.default_due_hours',
            'value' => '72',
            'created' => '2026-09-16 12:00:00',
            'modified' => '2026-09-16 12:00:00',
        ]];

        parent::init();
    }
}
