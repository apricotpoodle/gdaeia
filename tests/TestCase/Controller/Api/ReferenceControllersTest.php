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

    public function testUneLigneSocleNePeutPasEtreModifiee(): void
    {
        $this->authenticateSuperAdministrator();
        $this->getTableLocator()->get('Contracttypes')->updateAll(['deleted' => null], ['id' => 1]);
        $this->put('/api/contracttypes/1.json', [
            'code' => 'MODIFICATION-INTERDITE',
            'name' => 'Type socle modifié',
            'sort' => 'socle',
        ]);

        $this->assertResponseCode(403);
    }

    public function testUnUtilisateurStandardNePeutPasAdministrerLesReferentiels(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 1])]);
        $this->get('/api/contracttypes.json');

        $this->assertResponseCode(403);
    }

    private function authenticateSuperAdministrator(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
    }
}
