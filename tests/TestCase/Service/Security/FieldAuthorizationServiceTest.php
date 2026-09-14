<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Security;

use App\Model\Entity\FieldAuthorization;
use App\Model\Entity\User;
use App\Model\Table\FieldAuthorizationsTable;
use App\Service\Security\FieldAuthorizationService;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class FieldAuthorizationServiceTest extends TestCase
{
    private FieldAuthorizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FieldAuthorizationService();
    }

    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('FieldAuthorizations');
        parent::tearDown();
    }

    public function testLeSchemaDuSuperAdminEstVideEtNeFiltrePasLesDonnees(): void
    {
        $data = ['jobtitle' => 'Analyste', 'grossremuneration' => '50000'];

        $this->assertSame([], $this->service->getFieldSchema(new User(['issuperuser' => true]), 'Applicationforms'));
        $this->assertSame($data, $this->service->filterRequestData($data, []));
    }

    public function testLeFiltreConserveUniquementLesChampsExplicitementModifiables(): void
    {
        $data = [
            'jobtitle' => 'Analyste',
            'grossremuneration' => '50000',
            'archived' => '2026-01-01',
        ];

        $this->assertSame(
            ['jobtitle' => 'Analyste'],
            $this->service->filterRequestData($data, [
                'jobtitle' => 'EDIT',
                'grossremuneration' => 'VIEW',
                'archived' => 'NONE',
            ]),
        );
    }

    public function testLeSchemaDUnOperateurEstConstruitDepuisSaMatriceAcl(): void
    {
        $records = new ResultSet([
            new FieldAuthorization(['field' => 'jobtitle', 'access_level' => 'edit']),
            new FieldAuthorization(['field' => 'grossremuneration', 'access_level' => 'view']),
        ]);
        $query = $this->createMock(SelectQuery::class);
        $query->expects($this->once())->method('where')->with([
            'role_id' => 4,
            'resource' => 'Applicationforms',
        ])->willReturnSelf();
        $query->expects($this->once())->method('all')->willReturn($records);
        $authorizations = $this->createMock(FieldAuthorizationsTable::class);
        $authorizations->expects($this->once())->method('find')->willReturn($query);
        TableRegistry::getTableLocator()->set('FieldAuthorizations', $authorizations);

        $this->assertSame([
            'jobtitle' => 'EDIT',
            'grossremuneration' => 'VIEW',
        ], $this->service->getFieldSchema(new User([
            'issuperuser' => false,
            'role_id' => 4,
        ]), 'Applicationforms'));
    }

    public function testUnChampAbsentDeLaMatriceResteModifiable(): void
    {
        $this->assertSame(
            ['jobtitle' => 'Analyste', 'comment' => 'Information complementaire'],
            $this->service->filterRequestData([
                'jobtitle' => 'Analyste',
                'comment' => 'Information complementaire',
            ], ['jobtitle' => 'EDIT']),
        );
    }
}
