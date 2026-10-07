<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Helper;

use App\Model\Entity\FieldDefinition;
use App\View\Helper\TabulatorHelper;
use Authorization\IdentityInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use RuntimeException;

/** Vérifie le conteneur Tabulator et son indicateur d’autorisation. */
class TabulatorHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('Users');
        TableRegistry::getTableLocator()->remove('FieldDefinitions');
        parent::tearDown();
    }

    public function testRendUneGrilleAvecLesDroitsEtLesMetadonnees(): void
    {
        $this->installTables();
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('can')->willReturn(true);
        $request = (new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']))
            ->withAttribute('identity', $identity);
        $view = new View($request);
        /** @var TabulatorHelper $helper */
        $helper = $view->loadHelper('Tabulator', ['className' => TabulatorHelper::class]);

        $html = $helper->renderGrid('#Users-grid', 'Users');

        $this->assertStringContainsString('id="Users-grid"', $html);
        $this->assertStringContainsString('data-controller="users"', $html);
        $this->assertStringContainsString('data-can-create="true"', $html);
        $this->assertStringContainsString('Nom utilisateur', $html);
    }

    public function testRendUneGrilleSansDroitDeCreationSansIdentite(): void
    {
        $this->installTables();
        $view = new View(new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']));
        /** @var TabulatorHelper $helper */
        $helper = $view->loadHelper('Tabulator', ['className' => TabulatorHelper::class]);

        $this->assertStringContainsString('data-can-create="false"', $helper->renderGrid('#grid', 'Users'));
    }

    public function testRendUneGrilleSansDroitSiLaPolicyLeveUneException(): void
    {
        $this->installTables();
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('can')->willThrowException(new RuntimeException('Policy indisponible.'));
        $request = (new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']))
            ->withAttribute('identity', $identity);
        $view = new View($request);
        /** @var TabulatorHelper $helper */
        $helper = $view->loadHelper('Tabulator', ['className' => TabulatorHelper::class]);

        $this->assertStringContainsString('data-can-create="false"', $helper->renderGrid('#grid', 'Users'));
    }

    private function installTables(): void
    {
        $users = $this->createStub(Table::class);
        $users->method('newEmptyEntity')->willReturn(new Entity());
        TableRegistry::getTableLocator()->set('Users', $users);

        $query = $this->createStub(SelectQuery::class);
        $query->method('select')->willReturnSelf();
        $query->method('where')->willReturnSelf();
        $query->method('orderByAsc')->willReturnSelf();
        $query->method('all')->willReturn(new ResultSet([
            new FieldDefinition([
                'field' => 'name',
                'label' => 'Nom utilisateur',
                'description' => null,
            ]),
        ]));
        $definitions = $this->createStub(Table::class);
        $definitions->method('find')->willReturn($query);
        TableRegistry::getTableLocator()->set('FieldDefinitions', $definitions);
    }
}
