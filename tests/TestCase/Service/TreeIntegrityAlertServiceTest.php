<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\TreeIntegrityAlertService;
use Cake\Core\Configure;
use Cake\Mailer\Transport\DebugTransport;
use Cake\Mailer\TransportFactory;
use Cake\Mailer\TransportRegistry;
use Cake\TestSuite\TestCase;

/** Tests unitaires de la validation de configuration des alertes TreeBehavior. */
class TreeIntegrityAlertServiceTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $originalConfiguration;

    private mixed $originalTransport;

    private TransportRegistry $originalRegistry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalConfiguration = (array)Configure::read('TreeIntegrity', []);
        $this->originalTransport = TransportFactory::getConfig('default');
        $this->originalRegistry = TransportFactory::getRegistry();
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

    public function testUneAlerteDeTestPeutEtreAccepteeParUnTransportDeDebug(): void
    {
        Configure::write('TreeIntegrity', [
            'alertRecipient' => 'infogestion@lemonde.fr',
            'instanceName' => 'gdaetf2-test',
        ]);
        TransportFactory::drop('default');
        TransportFactory::setConfig('default', ['className' => DebugTransport::class]);
        TransportFactory::setRegistry(new TransportRegistry());

        $this->assertTrue((new TreeIntegrityAlertService())->sendTest());
    }

    protected function tearDown(): void
    {
        Configure::write('TreeIntegrity', $this->originalConfiguration);
        TransportFactory::drop('default');
        TransportFactory::setConfig('default', $this->originalTransport);
        TransportFactory::setRegistry($this->originalRegistry);
        parent::tearDown();
    }
}
