<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/** Vérifie la route Web et le contenu minimal de l'export PDF des DAE. */
class ApplicationformsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Applicationforms',
        'app.Departments',
        'app.Users',
        'app.Contracttypes',
        'app.Hiringreasons',
        'app.Budgetfeatures',
        'app.Professionalcategories',
        'app.Worktimes',
        'app.Periods',
        'app.Yesnos',
        'app.UserDepartments',
        'app.Roles',
        'app.ValidationWorkflowRuns',
        'app.Applicationvalidationsteps',
        'app.Validations',
        'app.Validationstatuses',
    ];

    public function testLePdfEstRetournePourUnOperateurAutorise(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/viewpdf/1');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/pdf');
        $this->assertHeaderContains('Content-Disposition', 'inline; filename="dae-1.pdf"');
        $this->assertStringStartsWith('%PDF', (string)$this->_response->getBody());
    }

    public function testLePdfRefuseUneDemandeHorsPerimetre(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_DEMANDEUR])]);

        $this->get('/applicationforms/viewpdf/1');

        $this->assertResponseCode(403);
    }
}
