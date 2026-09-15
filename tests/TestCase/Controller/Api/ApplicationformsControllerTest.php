<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

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
    ];

    public function testLApiDesDemandesRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/applicationforms.json');

        $this->assertRedirectContains('/users/login');
    }

    public function testLApiDesDemandesRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
    }

    public function testUnOperateurSansDepartementNeVoitAucuneDemande(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"data":[]');
    }

    public function testLeLienDeCreationEstRenduParLaCommandeDeDomaine(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);

        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Nouvelle demande');
    }
}
