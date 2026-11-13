<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Period;
use App\Model\Entity\User;
use App\Model\Table\ApplicationformsTable;
use App\Service\ApplicationformDuplicationResult;
use Cake\Datasource\EntityInterface;
use Cake\TestSuite\Fixture\FixtureStrategyInterface;
use Cake\TestSuite\Fixture\TruncateStrategy;
use Cake\TestSuite\TestCase;
use RuntimeException;

/**
 * App\Model\Table\ApplicationformsTable Test Case
 */
class ApplicationformsTableTest extends TestCase
{
    /**
     * Rend les insertions visibles au moteur FULLTEXT MySQL.
     *
     * @return \Cake\TestSuite\Fixture\FixtureStrategyInterface
     */
    protected function getFixtureStrategy(): FixtureStrategyInterface
    {
        return new TruncateStrategy();
    }

    /**
     * Test subject
     *
     * @var \App\Model\Table\ApplicationformsTable
     */
    protected $Applicationforms;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Departments',
        'app.Users',
        'app.Roles',
        'app.UserDepartments',
        'app.Contracttypes',
        'app.Hiringreasons',
        'app.Budgetfeatures',
        'app.Professionalcategories',
        'app.Worktimes',
        'app.Periods',
        'app.Yesnos',
        'app.Applicationvalidationsteps',
        'app.Validations',
        'app.WorkflowSettings',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Applicationforms') ? [] : ['className' => ApplicationformsTable::class];
        $this->Applicationforms = $this->getTableLocator()->get('Applicationforms', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Applicationforms);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ApplicationformsTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $applicationform = $this->Applicationforms->newEntity([
            'department_id' => 1,
            'user_id' => 1,
            'contracttype_id' => 1,
            'hiringreason_id' => 1,
            'budgetfeature_id' => 1,
            'jobtitle' => '',
            'professionalcategory_id' => 1,
            'worktime_id' => 1,
            'grossremuneration' => 'invalide',
            'period_id' => 1,
            'begin_at' => '2026-03-10',
            'end_at' => '2026-03-09',
            'yesno_id' => 1,
        ]);

        $this->assertArrayHasKey('jobtitle', $applicationform->getErrors());
        $this->assertArrayHasKey('grossremuneration', $applicationform->getErrors());
        $this->assertArrayHasKey('end_at', $applicationform->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ApplicationformsTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $applicationform = $this->Applicationforms->newEntity($this->validData([
            'department_id' => 99999,
        ]));

        $this->assertFalse($this->Applicationforms->save($applicationform));
        $this->assertArrayHasKey('department_id', $applicationform->getErrors());
    }

    public function testLesAssociationsFacultativesNeRetirentPasLaDemandeAvecContain(): void
    {
        $this->Applicationforms->getConnection()->update('applicationforms', [
            'budgetfeature_id' => null,
            'yesno_id' => null,
            'deleted' => null,
        ], ['id' => 1]);

        $applicationform = $this->Applicationforms->find()
            ->contain(['Budgetfeatures', 'Yesnos'])
            ->where(['Applicationforms.id' => 1])
            ->first();

        $this->assertInstanceOf(Applicationform::class, $applicationform);
        $this->assertNull($applicationform->get(Applicationform::FIELD_BUDGETFEATURE));
        $this->assertNull($applicationform->get(Applicationform::FIELD_YESNO));
    }

    public function testLaSauvegardeRefuseUneAssistanteNonRattacheeOuInactiveOuAuMauvaisRole(): void
    {
        $connection = $this->Applicationforms->getConnection();
        $connection->update('departments', ['deleted' => null], ['id' => 2]);
        $connection->update('roles', ['deleted' => null], ['id' => User::ROLE_ADMIN]);
        $connection->insert('roles', [
            'id' => User::ROLE_DEMANDEUR,
            'base' => 1,
            'code' => 'DEMANDEUR',
            'name' => 'Demandeur',
            'sort' => 'Demandeur',
            'deleted' => null,
            'created' => '2026-07-03 09:01:32',
            'modified' => '2026-07-03 09:01:32',
        ]);

        foreach ([3, 4, 5] as $id) {
            $connection->insert('users', [
                'id' => $id,
                'username' => 'candidat-assistante-' . $id,
                'email' => 'candidat-assistante-' . $id . '@example.test',
                'password' => 'mot-de-passe',
                'firstname' => 'Assistante',
                'lastname' => (string)$id,
                'issuperuser' => 0,
                'role_id' => $id === 4 ? User::ROLE_ADMIN : User::ROLE_DEMANDEUR,
                'deleted' => $id === 5 ? '2026-07-03 09:01:33' : null,
                'created' => '2026-07-03 09:01:33',
                'modified' => '2026-07-03 09:01:33',
            ]);
        }
        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveManyOrFail($userDepartments->newEntities([
            ['user_id' => 3, 'department_id' => 2],
            ['user_id' => 4, 'department_id' => 2],
            ['user_id' => 5, 'department_id' => 2],
        ]));
        $connection->insert('departments', [
            'id' => 3,
            'parent_id' => null,
            'lft' => 5,
            'rght' => 6,
            'level' => 1,
            'base' => 1,
            'code' => 'AUTRE-SERVICE',
            'name' => 'Autre service',
            'sort' => 'Autre service',
            'department_type_id' => 1,
            'deleted' => null,
        ]);
        $connection->insert('user_departments', [
            'user_id' => 6,
            'department_id' => 3,
            'created' => '2026-07-03 09:01:33',
            'modified' => '2026-07-03 09:01:33',
        ]);
        $connection->insert('users', [
            'id' => 6,
            'username' => 'assistante-autre-departement',
            'email' => 'assistante-autre-departement@example.test',
            'password' => 'mot-de-passe',
            'firstname' => 'Assistante',
            'lastname' => 'Autre département',
            'issuperuser' => 0,
            'role_id' => User::ROLE_DEMANDEUR,
            'deleted' => null,
            'created' => '2026-07-03 09:01:33',
            'modified' => '2026-07-03 09:01:33',
        ]);

        $eligible = $this->Applicationforms->newEntity($this->validData([
            'department_id' => 2,
            'assistant_id' => 3,
        ]));
        $this->assertNotFalse($this->Applicationforms->save($eligible));

        foreach ([4, 5, 6] as $assistantId) {
            $applicationform = $this->Applicationforms->newEntity($this->validData([
                'department_id' => 2,
                'assistant_id' => $assistantId,
            ]));

            $this->assertFalse($this->Applicationforms->save($applicationform));
            $this->assertArrayHasKey('assistant_id', $applicationform->getErrors());
        }
    }

    public function testUneModificationSansChangementAffectationConserveUneDaeExistante(): void
    {
        $connection = $this->Applicationforms->getConnection();
        $connection->update('applicationforms', ['assistant_id' => 2, 'deleted' => null], ['id' => 1]);
        $applicationform = $this->Applicationforms->get(1);
        $this->Applicationforms->patchEntity($applicationform, ['jobtitle' => 'Poste mis à jour']);

        $this->assertNotFalse($this->Applicationforms->save($applicationform));
        $this->assertSame(2, (int)$applicationform->get(Applicationform::FIELD_ASSISTANT_ID));
    }

    /**
     * La recherche FULLTEXT normalise plusieurs termes et exige leur présence.
     *
     * @return void
     */
    public function testRechercheFulltextAvecPlusieursTermes(): void
    {
        $this->Applicationforms->saveOrFail($this->Applicationforms->newEntity($this->validData([
            'jobtitle' => 'Architecte logiciel securise',
            'applicantname' => 'Camille Martin',
        ])));
        $this->Applicationforms->saveOrFail($this->Applicationforms->newEntity($this->validData([
            'jobtitle' => 'Analyste financier',
            'applicantname' => 'Morgan Dupont',
        ])));

        $matches = $this->Applicationforms->find('search', search: ['q' => ' architecte   secur '])
            ->orderByAsc('Applicationforms.id')
            ->all()
            ->toList();
        $noMatch = $this->Applicationforms->find('search', search: ['q' => 'architecte financier'])
            ->all()
            ->toList();

        $this->assertCount(1, $matches);
        $this->assertSame('Architecte logiciel securise', $matches[0]->jobtitle);
        $this->assertCount(0, $noMatch);
    }

    /**
     * Une recherche vide ne doit pas ajouter de critère FULLTEXT.
     *
     * @return void
     */
    public function testRechercheFulltextVideConserveToutesLesDemandes(): void
    {
        $this->Applicationforms->saveOrFail($this->Applicationforms->newEntity($this->validData([
            'jobtitle' => 'Charge de recrutement',
        ])));

        $results = $this->Applicationforms->find('search', search: ['q' => '   '])
            ->all()
            ->toList();

        $this->assertCount(2, $results);
    }

    public function testLaSauvegardeSynchroniseLeNomHistoriqueV3(): void
    {
        $applicationform = $this->Applicationforms->newEntity($this->validData([
            'applicant_first_name' => 'Ada',
            'applicant_last_name' => 'Lovelace',
        ]));

        $this->assertSame('Ada', $applicationform->applicant_first_name);
        $this->assertSame('Lovelace', $applicationform->applicant_last_name);

        $this->Applicationforms->saveOrFail($applicationform);

        $this->assertSame('Ada Lovelace', $applicationform->applicantname);
    }

    public function testLaSauvegardeCalculeLeSalaireDeBaseDepuisLaPeriodicite(): void
    {
        $this->getTableLocator()->get('Periods')->updateAll(
            ['code' => Period::CODE_EUR_PER_DAY],
            ['id' => 1],
        );
        $applicationform = $this->Applicationforms->newEntity($this->validData([
            'grossremuneration' => '125,50',
            'period_id' => 1,
            'working_days' => 4,
        ]));

        $this->Applicationforms->saveOrFail($applicationform);

        $this->assertSame('502.0000', $applicationform->salary_base);
    }

    public function testLaSauvegardeAccepteUnNombreDeJoursTransmisCommeChaine(): void
    {
        $this->getTableLocator()->get('Periods')->updateAll(
            ['code' => Period::CODE_EUR_PER_DAY],
            ['id' => 1],
        );
        $applicationform = $this->Applicationforms->newEntity($this->validData([
            'grossremuneration' => '2.00',
            'period_id' => 1,
        ]));
        $applicationform->set(Applicationform::FIELD_WORKING_DAYS, '2');

        $this->Applicationforms->saveOrFail($applicationform);

        $this->assertSame('4.0000', $applicationform->salary_base);
    }

    public function testLaDuplicationEffaceLesChampsAdministratifsEtRattacheLaCopie(): void
    {
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Contracttypes')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Professionalcategories')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Periods')->updateAll(['deleted' => null], ['id' => 1]);
        $source = $this->Applicationforms->get(1);
        $source->patch([
            Applicationform::FIELD_USER_ID => 1,
            Applicationform::FIELD_ASSISTANT_ID => 2,
            Applicationform::FIELD_MANAGER_ID => 2,
            Applicationform::FIELD_APPLICANTNAME => 'Candidat source',
            Applicationform::FIELD_SALARY_BASE => '1250.00',
            Applicationform::FIELD_HR_DESCRIPTION => 'Note RH',
            Applicationform::FIELD_CONTROL_MANAGEMENT_VALIDATION => 'Visa contrôle',
            Applicationform::FIELD_FINAL_ARGUMENT => 'Argumentaire',
            Applicationform::FIELD_BUDGETFEATURE_ID => 1,
            Applicationform::FIELD_YESNO_ID => 1,
            Applicationform::FIELD_QUALIFICATION => 'Qualification source',
        ]);

        $result = $this->Applicationforms->duplicateFor($source, new User(['id' => 2]));

        $this->assertInstanceOf(ApplicationformDuplicationResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->applicationform);
        $this->assertSame(2, $result->applicationform->get(Applicationform::FIELD_USER_ID));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_ASSISTANT_ID));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_MANAGER_ID));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_APPLICANTNAME));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_SALARY_BASE));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_HR_DESCRIPTION));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_CONTROL_MANAGEMENT_VALIDATION));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_FINAL_ARGUMENT));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_BUDGETFEATURE_ID));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_YESNO_ID));
        $this->assertNull($result->applicationform->get(Applicationform::FIELD_QUALIFICATION));
    }

    public function testUnBrouillonPeutEtreEnregistreSansChampsReserves(): void
    {
        $applicationform = $this->Applicationforms->newEntity($this->validData([
            Applicationform::FIELD_BUDGETFEATURE_ID => null,
            Applicationform::FIELD_YESNO_ID => null,
        ]));

        $this->assertNotFalse(
            $this->Applicationforms->save($applicationform),
            json_encode($applicationform->getErrors(), JSON_THROW_ON_ERROR),
        );
        $this->assertNull($applicationform->get(Applicationform::FIELD_BUDGETFEATURE_ID));
        $this->assertNull($applicationform->get(Applicationform::FIELD_YESNO_ID));
    }

    public function testLaDuplicationRefuseUneReferenceSupprimee(): void
    {
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Professionalcategories')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Periods')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Contracttypes')->updateAll(
            ['deleted' => '2026-11-05 12:00:00'],
            ['id' => 1],
        );
        $source = $this->Applicationforms->get(1);

        $result = $this->Applicationforms->duplicateFor($source, new User(['id' => 2]));

        $this->assertFalse($result->isSuccess());
        $this->assertSame(ApplicationformDuplicationResult::CATEGORY_REFERENCE, $result->category);
        $this->assertSame('contracttype_id', $result->errors[0]['field']);
        $this->assertStringContainsString('supprimée', $result->message);
    }

    public function testUneErreurDePersistanceNeExposePasLeDetailTechnique(): void
    {
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Contracttypes')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Professionalcategories')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Periods')->updateAll(['deleted' => null], ['id' => 1]);
        $source = $this->Applicationforms->get(1);
        $table = new class extends ApplicationformsTable {
            public function save(EntityInterface $entity, array $options = []): EntityInterface|false
            {
                throw new RuntimeException('détail SQL sensible');
            }
        };

        $result = $table->duplicateFor($source, new User(['id' => 2]));

        $this->assertFalse($result->isSuccess());
        $this->assertSame(ApplicationformDuplicationResult::CATEGORY_PERSISTENCE, $result->category);
        $this->assertSame([], $result->errors);
        $this->assertStringNotContainsString('détail SQL sensible', $result->message);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_replace([
            'department_id' => 1,
            'user_id' => 1,
            'contracttype_id' => 1,
            'hiringreason_id' => 1,
            'budgetfeature_id' => 1,
            'jobtitle' => 'Analyste recrutement',
            'professionalcategory_id' => 1,
            'worktime_id' => 1,
            'grossremuneration' => '45000.00',
            'period_id' => 1,
            'yesno_id' => 1,
        ], $overrides);
    }
}
