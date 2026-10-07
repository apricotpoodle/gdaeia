<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\Api\FieldAuthorizationsController
 */
class FieldAuthorizationsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = ['app.Roles', 'app.FieldAuthorizations', 'app.FieldDefinitions'];

    public function testLApiDesAutorisationsRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/field-authorizations.json');

        $this->assertRedirectContains('/users/login');
    }

    public function testLApiDesAutorisationsRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/field-authorizations.json?page=1&size=20');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Intitulé du poste', $payload['data'][0]['field_label']);
    }

    public function testLApiDesAutorisationsRefuseUnOperateurNonSuperAdmin(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->get('/api/field-authorizations.json');

        $this->assertResponseCode(403);
    }

    public function testLeFormulaireWebEstReserveAuSuperAdministrateur(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/field-authorizations/add');

        $this->assertResponseOk();
        $this->assertResponseContains('id="field-authorization-form"');
        $this->assertResponseContains('name="resource"');
        $this->assertResponseContains('Modification');
        $this->assertResponseContains('views/FieldAuthorizations/create.js');
    }

    public function testLeFormulaireWebEstRefuseAUnOperateurNonSuperAdministrateur(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);

        $this->get('/field-authorizations/add');

        $this->assertResponseCode(403);
    }

    public function testLApiPresenteLesErreursDeValidationDUneAutorisation(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/field-authorizations/add.json', [
            'role_id' => 1,
            'resource' => '',
            'field' => 'email',
            'access_level' => 'EDIT',
        ]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('Champ « Ressource » :');
        $this->assertResponseContains('"field":"resource","label":"Ressource","reason":');
    }

    public function testLeSuperAdministrateurPeutCreerModifierEtSupprimerUneAutorisation(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/field-authorizations/add.json', [
            'role_id' => 1,
            'resource' => 'Users',
            'field' => 'email',
            'access_level' => 'VIEW',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $this->assertResponseContains('"message":"La règle d’autorisation a été créée');

        $table = $this->getTableLocator()->get('FieldAuthorizations');
        $record = $table->find()->where(['resource' => 'Users', 'field' => 'email'])->firstOrFail();

        $this->put('/api/field-authorizations/edit/' . $record->id . '.json', [
            'access_level' => 'EDIT',
        ]);
        $this->assertResponseOk();

        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->post('/field-authorizations/delete/' . $record->id, []);
        $this->assertResponseOk();
        $this->assertSame(0, $table->find()->where(['id' => $record->id])->count());
    }

    public function testLApiRefuseUnNiveauDAutorisationInvalide(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/field-authorizations/add.json', [
            'role_id' => 1,
            'resource' => 'Users',
            'field' => 'email',
            'access_level' => 'ADMIN',
        ]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('Le niveau d’accès est invalide.');
    }
}
