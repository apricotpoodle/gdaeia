<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Security;

use App\Model\Entity\User;
use App\Service\Security\FieldAuthorizationService;
use Cake\TestSuite\TestCase;

class FieldAuthorizationServiceTest extends TestCase
{
    private FieldAuthorizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FieldAuthorizationService();
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
}
