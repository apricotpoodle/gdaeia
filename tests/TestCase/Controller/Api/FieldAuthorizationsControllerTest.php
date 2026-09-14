<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class FieldAuthorizationsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function testLApiDesAutorisationsRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/field-authorizations.json');

        $this->assertRedirectContains('/users/login');
    }

    public function testLApiDesAutorisationsRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/field-authorizations.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
    }

    public function testLApiDesAutorisationsRefuseUnOperateurNonSuperAdmin(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->get('/api/field-authorizations.json');

        $this->assertResponseCode(403);
    }
}
