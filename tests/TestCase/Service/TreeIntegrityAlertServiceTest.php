<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\TreeIntegrityAlertService;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/** Tests unitaires de la validation de configuration des alertes TreeBehavior. */
class TreeIntegrityAlertServiceTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $originalConfiguration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalConfiguration = (array)Configure::read('TreeIntegrity', []);
    }

    public function testLaConfigurationValideNeProduitAucuneErreur(): void
    {
        Configure::write('TreeIntegrity', [
            'alertRecipient' => 'infogestion@lemonde.fr',
            'instanceName' => 'gdaetf2-test',
        ]);

        $this->assertSame([], (new TreeIntegrityAlertService())->configurationErrors());
    }

    public function testLaConfigurationAbsenteEstExplicite(): void
    {
        Configure::write('TreeIntegrity', [
            'alertRecipient' => '',
            'instanceName' => '',
        ]);

        $this->assertSame([
            'TREE_INTEGRITY_ALERT_RECIPIENT est obligatoire.',
            'APP_INSTANCE_NAME est obligatoire pour identifier l’instance dans les alertes.',
        ], (new TreeIntegrityAlertService())->configurationErrors());
    }

    protected function tearDown(): void
    {
        Configure::write('TreeIntegrity', $this->originalConfiguration);
        parent::tearDown();
    }
}
