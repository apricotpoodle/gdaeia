<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class MenusControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Menus',
        'app.Roles',
        'app.RoleMenus',
    ];

    /** Vérifie que l'API historique des menus protège ses données d'un visiteur. */
    public function testLApiDesMenusRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/menus.json');

        $this->assertRedirectContains('/users/login');
    }

    /** Vérifie le contrat JSON de la grille de menus pour un opérateur identifié. */
    public function testLApiDesMenusRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/menus.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
    }

    /** Vérifie que l'attribution couvre aussi les descendants du menu sélectionné. */
    public function testLApiAttribueUnRoleAuMenuSelectionne(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->getTableLocator()->get('Roles')->updateAll(['deleted' => null], ['id' => 1]);
        $menus = $this->getTableLocator()->get('Menus');
        $rootMenu = $menus->get(1);
        $rootMenu->parent_id = null;
        $menus->saveOrFail($rootMenu);
        $childMenu = $menus->newEntity([
            'parent_id' => 1,
            'name' => 'Sous-option attribuable',
            'url' => '/sous-option',
            'active' => true,
        ]);
        $menus->saveOrFail($childMenu);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/menus/assign-role-access.json', [
            'menu_ids' => [1],
            'role_id' => 1,
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"associations_created":1');
        $this->assertSame(1, $this->getTableLocator()->get('RoleMenus')->find()
            ->where(['role_id' => 1, 'menu_id' => $childMenu->id, 'department_id IS' => null])
            ->count());
    }

    /** Vérifie l'accès HTTP à l'écran d'administration pour un super-administrateur. */
    public function testLEcranDAffectationEstAccessibleParUnSuperAdministrateur(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/menus/role-access');

        $this->assertResponseOk();
        $this->assertResponseContains('Options de menu et rôles');
    }

    /** Vérifie le rendu du point d'entrée autorisé depuis la liste des menus. */
    public function testLeLienDAffectationEstRenduPourUnOperateurAutorise(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/menus');

        $this->assertResponseOk();
        $this->assertResponseContains('Associer aux rôles');
    }

    /** Vérifie que les associations départementales rendent aussi le rôle associé. */
    public function testLApiConsidereAussiLesAssociationsLimiteesAUnDepartement(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->getTableLocator()->get('Roles')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/api/menus/role-access-assigned-roles.json?menu_ids[]=1');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum dolor sit amet');
    }
}
