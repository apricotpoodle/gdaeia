<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApplicationformsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ApplicationformsTable Test Case
 */
class ApplicationformsTableTest extends TestCase
{
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
        'app.Contracttypes',
        'app.Hiringreasons',
        'app.Budgetfeatures',
        'app.Professionalcategories',
        'app.Worktimes',
        'app.Periods',
        'app.Yesnos',
        'app.Applicationvalidationsteps',
        'app.Validations',
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
