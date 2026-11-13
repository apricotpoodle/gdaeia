<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/** Vérifie le contrat API commun aux sept référentiels métier. */
class ReferenceControllersTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures des sept nomenclatures et de l’authentification.
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Contracttypes',
        'app.Hiringreasons',
        'app.Professionalcategories',
        'app.Worktimes',
        'app.Periods',
        'app.Budgetfeatures',
        'app.Yesnos',
    ];

    /** @return array<string, array{0: string, 1: string}> */
    public static function references(): array
    {
        return [
            'types de contrats' => ['contracttypes', 'Contracttypes'],
            'motifs de recrutement' => ['hiringreasons', 'Hiringreasons'],
            'catégories professionnelles' => ['professionalcategories', 'Professionalcategories'],
            'temps de travail' => ['worktimes', 'Worktimes'],
            'périodicités' => ['periods', 'Periods'],
            'caractéristiques budgétaires' => ['budgetfeatures', 'Budgetfeatures'],
            'réponses oui non' => ['yesnos', 'Yesnos'],
        ];
    }

    public function testUnSuperAdministrateurPeutConsulterEtModifierChaqueReferentiel(): void
    {
        $this->authenticateSuperAdministrator();
        foreach (self::references() as [$slug, $alias]) {
            $table = $this->getTableLocator()->get($alias);
            $entity = $table->newEntity([
                'code' => 'TST',
                'name' => 'Référence de test',
                'sort' => 'test',
                'base' => false,
            ]);
            $this->assertNotFalse($table->save($entity));
            $id = (int)$entity->get('id');

            $this->get('/api/' . $slug . '.json');
            $this->assertResponseOk();
            $this->assertResponseContains('"data"');

            $this->put('/api/' . $slug . '/' . $id . '.json', [
                'code' => 'TST',
                'name' => 'Référence modifiée',
                'sort' => 'modifie',
            ]);
            $this->assertResponseOk();
            $this->assertResponseContains('"success":true');

            $this->delete('/api/' . $slug . '/' . $id . '.json');
            $this->assertResponseOk();
            $this->assertResponseContains('"success":true');
        }
    }

    public function testUneLigneSocleRefuseUneModificationCombineeAvecUneErreurDeValidation(): void
    {
        $this->authenticateSuperAdministrator();
        $this->getTableLocator()->get('Contracttypes')->updateAll(['deleted' => null], ['id' => 1]);
        $this->put('/api/contracttypes/1.json', [
            'base' => false,
            'code' => 'MODIFICATION',
            'name' => 'Type socle modifié',
            'sort' => 'socle',
        ]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"success":false');
        $this->assertResponseContains('"errors":');
        $reference = $this->getTableLocator()->get('Contracttypes')->get(1);
        $this->assertTrue($reference->base);
        $this->assertSame('Lorem ipsum do', $reference->code);
    }

    public function testUneBasculeDeSocleRefuseUnIdentifiantModifie(): void
    {
        $this->authenticateSuperAdministrator();
        $table = $this->getTableLocator()->get('Contracttypes');
        $table->updateAll(['deleted' => null], ['id' => 1]);

        $this->put('/api/contracttypes/1.json', ['base' => false, 'id' => 999]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"errors":');
        $this->assertTrue($table->get(1)->base);
    }

    public function testLeSuperAdministrateurPeutCreerEtBasculerLeSocleDeChaqueReferentiel(): void
    {
        $this->authenticateSuperAdministrator();
        foreach (self::references() as [$slug, $alias]) {
            $this->post('/api/' . $slug . '.json', [
                'code' => 'BASE', 'name' => 'Référence socle', 'sort' => 'base', 'base' => true,
            ]);
            $this->assertResponseOk();
            $table = $this->getTableLocator()->get($alias);
            $reference = $table->find()->where(['code' => 'BASE'])->firstOrFail();
            $this->assertTrue($reference->base, $alias);

            $this->get('/api/' . $slug . '/' . $reference->id . '.json');
            $this->assertResponseOk();
            $this->assertResponseRegExp('/"base"\s*:\s*true/');

            $this->put('/api/' . $slug . '/' . $reference->id . '.json', ['base' => false]);
            $this->assertResponseOk();
            $this->assertFalse($table->get($reference->id)->base, $alias);

            $this->put('/api/' . $slug . '/' . $reference->id . '.json', ['base' => true]);
            $this->assertResponseOk();
            $this->assertTrue($table->get($reference->id)->base, $alias);

            $this->delete('/api/' . $slug . '/' . $reference->id . '.json');
            $this->assertResponseCode(403);
        }
    }

    public function testLeFormulaireWebAfficheLeSocleEtVerrouilleLesChampsOrdinaires(): void
    {
        $this->authenticateSuperAdministrator();
        $this->getTableLocator()->get('Contracttypes')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/contracttypes/add');
        $this->assertResponseOk();
        $this->assertResponseContains('name="base"');
        $this->assertResponseContains('Référence socle');

        $this->get('/contracttypes/edit/1');
        $this->assertResponseOk();
        $this->assertResponseContains('name="base"');
        foreach (['code', 'name', 'sort'] as $field) {
            $this->assertResponseRegExp('/<input(?=[^>]*name="' . $field . '")(?=[^>]*readonly)[^>]*>/');
        }
    }

    public function testUnUtilisateurStandardNePeutPasAdministrerLesReferentiels(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 1])]);
        $this->get('/api/contracttypes.json');

        $this->assertResponseCode(403);
    }

    public function testUnUtilisateurStandardNePeutPasCreerUneReferenceSocleParPost(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->post('/api/contracttypes.json', [
            'code' => 'SOCLEFORGE', 'name' => 'Socle forgé', 'sort' => 'socle', 'base' => true,
        ]);

        $this->assertResponseCode(403);
        $this->assertSame(
            0,
            $this->getTableLocator()->get('Contracttypes')->find()->where(['code' => 'SOCLEFORGE'])->count(),
        );
    }

    public function testLesIndexWebDesSeptReferentielsUtilisentLeTemplatePartage(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        foreach (self::references() as [$slug]) {
            $this->get('/' . $slug);
            $this->assertResponseOk();
        }
    }

    private function authenticateSuperAdministrator(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
    }
}
