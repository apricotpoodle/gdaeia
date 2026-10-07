<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\Api\MenusController
 */
class MenusControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.FieldDefinitions',
        'app.Menus',
        'app.Roles',
        'app.RoleMenus',
    ];

    /** Vérifie que l'API historique des menus protège ses données d'un visiteur. */
    public function testLApiDesMenusRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/menus/grid.json');

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

    /** Vérifie le contrat distant de la grille et la pagination des racines. */
    public function testLApiDeGrilleDesMenusRetourneUnePageEtSonDernierNumero(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $menus = $this->getTableLocator()->get('Menus');
        $menus->saveOrFail($menus->newEntity([
            'name' => 'Deuxième racine',
            'url' => '/deuxieme-racine',
            'active' => true,
        ]));

        $this->get('/api/menus/grid.json?size=1&page=1');

        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(1, $payload['data']);
        $this->assertSame(2, $payload['last_page']);
    }

    /** Vérifie qu'un filtre sur un descendant conserve le contexte de sa branche. */
    public function testLeFiltreDeLaGrilleDesMenusConserveLesAncetresDuResultat(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $menus = $this->getTableLocator()->get('Menus');
        $root = $menus->get(1);
        $root->parent_id = null;
        $menus->saveOrFail($root);
        $child = $menus->newEntity([
            'parent_id' => 1,
            'name' => 'Résultat filtré',
            'url' => '/resultat-filtre',
            'active' => true,
        ]);
        $menus->saveOrFail($child);

        $filters = rawurlencode(json_encode([
            ['field' => 'name', 'type' => 'like', 'value' => 'Résultat filtré'],
        ], JSON_THROW_ON_ERROR));
        $this->get('/api/menus/grid.json?filters=' . $filters);

        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(1, $payload['data']);
        $this->assertSame('Lorem ipsum dolor sit amet', $payload['data'][0]['name']);
        $this->assertSame('Résultat filtré', $payload['data'][0]['children'][0]['name']);
    }

    /** Vérifie que le filtre numérique du niveau ne provoque pas d'erreur API. */
    public function testLeFiltreDuNiveauDesMenusEstTraiteCommeUnEntier(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $filters = rawurlencode(json_encode([
            ['field' => 'level', 'type' => '=', 'value' => '0'],
        ], JSON_THROW_ON_ERROR));

        $this->get('/api/menus/grid.json?filters=' . $filters);

        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertNotEmpty($payload['data']);
        $this->assertSame(0, $payload['data'][0]['level']);
    }

    /** Vérifie qu'un administrateur non super-utilisateur est refusé par l'API. */
    public function testLApiDesMenusRefuseUnAdministrateurOrdinaire(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/api/menus/grid.json');

        $this->assertResponseCode(403);
    }

    /** Vérifie le repli et le message de refus de l'administration Web. */
    public function testLEcranDesMenusRedirigeUnAdministrateurOrdinaire(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/menus');

        $this->assertRedirect('/');
        $this->assertSession('Vous n’êtes pas autorisé à administrer les menus.', 'Flash.flash.0.message');
    }

    public function testLeFormulaireWebAfficheLeChampInvalideDuMenu(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/menus/add', ['name' => str_repeat('M', 256), 'url' => '/menu']);

        $this->assertResponseOk();
        $this->assertResponseContains('Champ « Nom du menu » :');
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
