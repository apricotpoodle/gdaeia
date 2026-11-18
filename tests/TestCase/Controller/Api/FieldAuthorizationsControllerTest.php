<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\FieldAuthorization;
use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use DateTimeInterface;

/**
 * @link \App\Controller\Api\FieldAuthorizationsController
 */
class FieldAuthorizationsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = ['app.Roles', 'app.FieldAuthorizations', 'app.FieldDefinitions', 'app.AuthorizationTargets'];

    public function testLaModificationRetourneLaLigneActualiseePourTabulator(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/field-authorizations/edit/1.json', ['access_level' => 'VIEW']);

        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($payload['success']);
        $this->assertSame(1, $payload['data']['id']);
        $this->assertSame('VIEW', $payload['data']['access_level']);
        $this->assertArrayHasKey('role', $payload['data']);
    }

    public function testLApiRetourneLeNiveauLectureSeuleSousLaValeurCanoniqueView(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/field-authorizations/edit/1.json', ['access_level' => 'view']);

        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(FieldAuthorization::ACCESS_LEVEL_VIEW, $payload['data']['access_level']);
    }

    public function testLEditionInlineAccepteLesLibellesFrancaisDesNiveaux(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        foreach (
            [
                'Modification' => FieldAuthorization::ACCESS_LEVEL_EDIT,
                'Lecture seule' => FieldAuthorization::ACCESS_LEVEL_VIEW,
                'Aucun accès' => FieldAuthorization::ACCESS_LEVEL_NONE,
            ] as $label => $expectedLevel
        ) {
            $this->post('/api/field-authorizations/edit/1.json', ['access_level' => $label]);

            $this->assertResponseOk();
            $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $this->assertTrue($payload['success']);
            $this->assertSame($expectedLevel, $payload['data']['access_level']);
        }
    }

    public function testLApiDesAutorisationsSignaleUnePerteDeSessionEnJson(): void
    {
        $this->get('/api/field-authorizations.json');

        $this->assertResponseCode(401);
        $this->assertResponseContains('Votre session a expiré. Veuillez vous reconnecter.');
    }

    public function testLApiDesAutorisationsRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/field-authorizations.json?page=1&size=20');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Intitulé du poste', $payload['data'][0]['target_label']);
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
        $this->assertResponseContains('data-schema-url="/api/field-authorizations/get-form-schema.json"');
        $this->assertResponseContains('name="resource"');
        $this->assertResponseContains('name="target_type"');
        $this->assertResponseContains('name="target"');
        $this->assertResponseContains('data-target-resource');
        $this->assertResponseContains('data-target-select');
        $this->assertResponseContains('data-target-description');
        $this->assertResponseContains('Champ ORM');
        $this->assertResponseContains('Zone fonctionnelle');
        $this->assertResponseContains('Modification');
        $this->assertResponseContains('views/FieldAuthorizations/create.js');
    }

    public function testLeFormulaireWebDEditionExposeLeMemeContratDeCatalogue(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/field-authorizations/edit/1');

        $this->assertResponseOk();
        $this->assertResponseContains('id="field-authorization-form"');
        $this->assertResponseContains('data-schema-url="/api/field-authorizations/get-form-schema.json"');
        $this->assertResponseContains('data-current-type="field"');
        $this->assertResponseContains('data-current-resource="Applicationforms"');
        $this->assertResponseContains('data-current-target="jobtitle"');
        $this->assertResponseContains('name="resource"');
        $this->assertResponseContains('name="target_type"');
        $this->assertResponseContains('name="target"');
        $this->assertResponseContains('data-target-resource');
        $this->assertResponseContains('data-target-select');
        $this->assertResponseContains('data-target-description');
        $this->assertResponseContains('views/FieldAuthorizations/edit.js');
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
        $record = $table->find()->where(['resource' => 'Users', 'target' => 'email'])->firstOrFail();

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

    public function testLeCatalogueNeRetourneQueLesCiblesActivesDesDeuxReferentiels(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->getTableLocator()->get('FieldDefinitions')->updateAll(['active' => false], ['resource' => 'Users']);
        $this->getTableLocator()->get('FieldDefinitions')->updateAll(
            ['active' => false],
            ['resource' => 'Applicationforms', 'field' => 'working_days'],
        );
        $this->getTableLocator()->get('AuthorizationTargets')->updateAll(['active' => false], ['target' => 'rh_section']);

        $this->get('/api/field-authorizations/get-form-schema.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $resources = $payload['resources'];
        $targets = $payload['targets'];

        $this->assertContains('Applicationforms', $resources, 'Une cible active conserve sa ressource dans le catalogue.');
        $this->assertNotContains('Users', $resources);
        $this->assertCount(count(array_unique($resources)), $resources);
        foreach ($targets as $target) {
            $this->assertContains($target['resource'], $resources);
        }

        $field = $this->findTarget($targets, 'Applicationforms', 'field', 'jobtitle');
        $this->assertSame('Intitulé du poste', $field['label']);
        $this->assertNull($this->findTargetOrNull($targets, 'Applicationforms', 'field', 'working_days'));
        $section = $this->findTarget($targets, 'Applicationforms', 'section', 'job_section');
        $this->assertSame('Zone métier', $section['label']);
        $this->assertSame('Zones métier.', $section['description']);
        $this->assertNull($this->findTargetOrNull($targets, 'Applicationforms', 'section', 'rh_section'));
        $this->assertNull($this->findTargetOrNull($targets, 'Users', 'field', 'email'));
    }

    public function testLeCatalogueEtLEditionConserventUneCibleHistoriqueInactive(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->getTableLocator()->get('FieldDefinitions')->updateAll(
            ['active' => false],
            ['resource' => 'Applicationforms', 'field' => 'jobtitle'],
        );

        $this->get('/api/field-authorizations/get-form-schema.json?current_resource=Applicationforms&current_target=jobtitle');

        $this->assertResponseOk();
        $catalog = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $historicalTarget = $this->findTarget($catalog['targets'], 'Applicationforms', 'field', 'jobtitle');
        $this->assertSame('Intitulé du poste', $historicalTarget['label']);

        $this->post('/api/field-authorizations/edit/1.json', ['access_level' => 'VIEW']);

        $this->assertResponseOk();
        $record = $this->getTableLocator()->get('FieldAuthorizations')->get(1);
        $this->assertSame('Applicationforms', $record->get('resource'));
        $this->assertSame('field', $record->get('target_type'));
        $this->assertSame('jobtitle', $record->get('target'));
        $this->assertSame('VIEW', $record->get('access_level'));
    }

    public function testLesCiblesFabriqueesInvalidesNeCreentNiNeModifientDeRegle(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->getTableLocator()->get('FieldDefinitions')->updateAll(
            ['active' => false],
            ['resource' => 'Applicationforms', 'field' => 'working_days'],
        );
        $this->getTableLocator()->get('AuthorizationTargets')->updateAll(
            ['active' => false],
            ['resource' => 'Applicationforms', 'target' => 'rh_section'],
        );

        $invalidTargets = [
            ['resource' => 'Applicationforms', 'target_type' => 'field', 'target' => 'unknown_field'],
            ['resource' => 'Applicationforms', 'target_type' => 'field', 'target' => 'working_days'],
            ['resource' => 'Applicationforms', 'target_type' => 'section', 'target' => 'unknown_section'],
            ['resource' => 'Applicationforms', 'target_type' => 'section', 'target' => 'rh_section'],
            ['resource' => 'Applicationforms', 'target_type' => 'field', 'target' => 'job_section'],
            ['resource' => 'Applicationforms', 'target_type' => 'section', 'target' => 'jobtitle'],
            ['resource' => 'Users', 'target_type' => 'field', 'target' => 'jobtitle'],
            ['resource' => 'Users', 'target_type' => 'section', 'target' => 'job_section'],
        ];
        $initialRows = $this->authorizationRowsSnapshot();

        foreach ($invalidTargets as $invalidTarget) {
            $this->post('/api/field-authorizations/add.json', $invalidTarget + [
                'role_id' => 1,
                'access_level' => 'VIEW',
            ]);

            $this->assertResponseCode(422);
            $this->assertSame($initialRows, $this->authorizationRowsSnapshot());
        }

        foreach ($invalidTargets as $invalidTarget) {
            $this->put('/api/field-authorizations/edit/1.json', $invalidTarget + [
                'access_level' => 'VIEW',
            ]);

            $this->assertResponseCode(422);
            $this->assertSame($initialRows, $this->authorizationRowsSnapshot());
        }
    }

    /** @return list<array<string, mixed>> */
    private function authorizationRowsSnapshot(): array
    {
        $table = $this->getTableLocator()->get('FieldAuthorizations');
        $records = $table->find()->orderByAsc('id')->all()->toList();

        return array_map(static function ($record): array {
            return array_map(
                static fn(mixed $value): mixed => $value instanceof DateTimeInterface
                    ? $value->format(DATE_ATOM)
                    : $value,
                $record->toArray(),
            );
        }, $records);
    }

    /** @param array<int, array<string, mixed>> $targets */
    private function findTarget(array $targets, string $resource, string $type, string $target): array
    {
        return $this->findTargetOrNull($targets, $resource, $type, $target)
            ?? $this->fail(sprintf('La cible %s/%s/%s est absente du catalogue.', $resource, $type, $target));
    }

    /** @param array<int, array<string, mixed>> $targets */
    private function findTargetOrNull(array $targets, string $resource, string $type, string $target): ?array
    {
        foreach ($targets as $catalogTarget) {
            if (
                $catalogTarget['resource'] === $resource
                && $catalogTarget['target_type'] === $type
                && $catalogTarget['target'] === $target
            ) {
                return $catalogTarget;
            }
        }

        return null;
    }
}
