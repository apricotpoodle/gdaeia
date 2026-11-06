<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Cibles fonctionnelles utilisées par les tests ACL. */
final class AuthorizationTargetsFixture extends TestFixture
{
    public string $table = 'authorization_targets';

    /** @inheritDoc */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'resource' => 'Applicationforms',
                'target_type' => 'section',
                'target' => 'job_section',
                'label' => 'Zone métier',
                'description' => 'Zones métier.',
                'active' => 1,
                'position' => 1,
                'created' => '2026-11-06 00:00:00',
                'modified' => '2026-11-06 00:00:00',
            ],
            [
                'id' => 2,
                'resource' => 'Applicationforms',
                'target_type' => 'section',
                'target' => 'rh_section',
                'label' => 'Zone RH et administration',
                'description' => 'Zone réservée.',
                'active' => 1,
                'position' => 2,
                'created' => '2026-11-06 00:00:00',
                'modified' => '2026-11-06 00:00:00',
            ],
        ];
        parent::init();
    }
}
