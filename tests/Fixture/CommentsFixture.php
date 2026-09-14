<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CommentsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [[
            'id' => 1,
            'parent_id' => null,
            'model' => 'Applicationforms',
            'foreign_key' => 1,
            'type' => 'GENERAL',
            'content' => 'Commentaire protege',
            'user_id' => 1,
            'created' => '2026-01-01 10:00:00',
            'modified' => null,
        ]];

        parent::init();
    }
}
