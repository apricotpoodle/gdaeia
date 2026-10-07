<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\FieldDefinition;
use Authorization\AuthorizationServiceInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Vérifie les comportements transversaux hérités par les contrôleurs. */
class AppControllerCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('FieldDefinitions');
        parent::tearDown();
    }

    public function testUneErreurDeValidationSansDetailProduitUnFlashGenerique(): void
    {
        $controller = $this->controller();

        $controller->flashErrors(new Entity(), 'Users');

        $messages = $controller->getRequest()->getFlash()->consume('flash');
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Impossible d’enregistrer les données', (string)$messages[0]['message']);
    }

    public function testUneErreurDeValidationSansDetailProduitUneReponse500(): void
    {
        $controller = $this->controller();

        $response = $controller->validationErrors(new Entity(), 'Users');

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Impossible d’enregistrer les données', (string)$response->getBody());
    }

    public function testUneErreurDeValidationDetailleeProduitUnFlashParChamp(): void
    {
        $this->installFieldMetadata();
        $controller = $this->controller();
        $entity = new Entity();
        $entity->setErrors(['jobtitle' => ['_empty' => 'Ce champ est obligatoire.']]);

        $controller->flashErrors($entity, 'Applicationforms');

        $messages = $controller->getRequest()->getFlash()->consume('flash');
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Intitulé du poste', (string)$messages[0]['message']);
    }

    public function testUneErreurDeValidationDetailleeProduitUneReponse422(): void
    {
        $this->installFieldMetadata();
        $controller = $this->controller();
        $entity = new Entity();
        $entity->setErrors(['jobtitle' => ['_empty' => 'Ce champ est obligatoire.']]);

        $response = $controller->validationErrors($entity, 'Applicationforms');
        $payload = json_decode((string)$response->getBody(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Intitulé du poste', $payload['errors'][0]['label']);
    }

    public function testBeforeRenderExposeLeFlagDUsurpation(): void
    {
        $controller = $this->controller();

        $controller->beforeRender(new Event('Controller.beforeRender', $controller));

        $this->assertFalse($controller->viewBuilder()->getVars()['isImpersonating']);
    }

    public function testBeforeFilterConfigureLesExceptionsDuPluginDeDebug(): void
    {
        $controller = $this->controller('DebugKit');

        $controller->beforeFilter(new Event('Controller.beforeFilter', $controller));

        $this->assertNotNull($controller->getRequest()->getAttribute('authorization'));
    }

    public function testLeFormateurDeDroitsRetourneLesActionsEtLesColonnes(): void
    {
        $controller = $this->controller();
        $formatter = $controller->rightsFormatter(['publish'], static function (
            EntityInterface $entity,
            object $authorization,
        ): array {
            return ['title' => true];
        });

        $result = $formatter(new Entity(['id' => 10]));

        $this->assertSame(['view', 'edit', 'delete', 'publish'], array_keys($result['actions']));
        $this->assertSame(['title' => true], $result['columns']);
    }

    private function controller(?string $plugin = null): TestableAppController
    {
        $authorization = $this->createStub(AuthorizationServiceInterface::class);
        $authorization->method('can')->willReturn(false);
        $authorization->method('skipAuthorization')->willReturnSelf();
        $request = new ServerRequest([
            'base' => '',
            'url' => '/',
            'webroot' => '/',
            'params' => array_filter(['plugin' => $plugin, 'action' => 'index']),
        ]);
        $request = $request->withAttribute('authorization', $authorization);

        return new TestableAppController($request);
    }

    private function installFieldMetadata(): void
    {
        $query = $this->createStub(SelectQuery::class);
        $query->method('select')->willReturnSelf();
        $query->method('where')->willReturnSelf();
        $query->method('orderByAsc')->willReturnSelf();
        $query->method('all')->willReturn(new ResultSet([
            new FieldDefinition([
                'field' => 'jobtitle',
                'label' => 'Intitulé du poste',
                'description' => null,
            ]),
        ]));
        $table = $this->createStub(Table::class);
        $table->method('find')->willReturn($query);
        TableRegistry::getTableLocator()->set('FieldDefinitions', $table);
    }
}
