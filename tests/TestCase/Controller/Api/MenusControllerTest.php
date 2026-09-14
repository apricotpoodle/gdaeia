<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class MenusControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function testLApiDesMenusRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/menus.json');

        $this->assertRedirectContains('/users/login');
    }

    public function testLApiDesMenusRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/menus.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
    }
}
