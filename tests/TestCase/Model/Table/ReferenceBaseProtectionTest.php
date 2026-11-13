<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ContracttypesTable;
use App\Model\Table\DepartmentsTable;
use App\Model\Table\RolesTable;
use Cake\TestSuite\TestCase;

/** Vérifie la protection ORM des enregistrements de référence du socle. */
class ReferenceBaseProtectionTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Budgetfeatures',
        'app.Contracttypes',
        'app.Departments',
        'app.Hiringreasons',
        'app.Periods',
        'app.Professionalcategories',
        'app.Roles',
        'app.Worktimes',
        'app.Yesnos',
    ];

    /** Un nouvel enregistrement de socle peut être créé. */
    public function testCreationInitialeDuSocle(): void
    {
        $table = $this->getTableLocator()->get('Contracttypes', ['className' => ContracttypesTable::class]);
        $record = $table->newEntity([
            'base' => true,
            'code' => 'SOCLE_NOUVEAU',
            'name' => 'Nouveau socle',
            'sort' => 'Nouveau socle',
        ]);

        $this->assertNotFalse($table->save($record));
        $this->assertTrue($table->get($record->id)->base);
    }

    /** Une bascule isolée de base est admise dans les deux sens. */
    public function testBasculeIsoleeDuStatutDeSocle(): void
    {
        $table = $this->getTableLocator()->get('Contracttypes', ['className' => ContracttypesTable::class]);
        $record = $table->newEntity([
            'base' => false,
            'code' => 'BASCULE',
            'name' => 'Bascule',
            'sort' => 'Bascule',
        ]);
        $this->assertNotFalse($table->save($record));

        $record->base = true;
        $this->assertNotFalse($table->save($record));
        $this->assertTrue($table->get($record->id)->base);

        $record->base = false;
        $this->assertNotFalse($table->save($record));
        $this->assertFalse($table->get($record->id)->base);
    }

    /** Une modification métier simultanée à la protection est refusée. */
    public function testRefusDesModificationsDuSocleEtDesBasculeCombinees(): void
    {
        $table = $this->getTableLocator()->get('Contracttypes', ['className' => ContracttypesTable::class]);
        $record = $table->get(1);
        $name = $record->name;
        $record->name = 'Nom modifié';
        $this->assertFalse($table->save($record));
        $this->assertSame($name, $table->get(1)->name);

        $record = $table->get(1);
        $record->base = false;
        $record->name = 'Nom modifié';
        $this->assertFalse($table->save($record));
        $this->assertTrue($table->get(1)->base);

        $local = $table->newEntity([
            'base' => false,
            'code' => 'PROMOTION',
            'name' => 'Promotion',
            'sort' => 'Promotion',
        ]);
        $this->assertNotFalse($table->save($local));
        $local->base = true;
        $local->name = 'Promotion modifiée';
        $this->assertFalse($table->save($local));
        $this->assertFalse($table->get($local->id)->base);
    }

    /** Le tri d'une référence de socle reste inchangé après un refus. */
    public function testRefusDeModifierLeTriDuSocle(): void
    {
        $table = $this->getTableLocator()->get('Contracttypes', ['className' => ContracttypesTable::class]);
        $record = $table->get(1);
        $sort = $record->sort;
        $record->sort = 'Tri modifié';

        $this->assertFalse($table->save($record));
        $this->assertSame($sort, $table->get(1)->sort);
    }

    /** L'identifiant est stable et le code l'est dès qu'un socle est concerné. */
    public function testIdentifiantEtCodeProteges(): void
    {
        $table = $this->getTableLocator()->get('Contracttypes', ['className' => ContracttypesTable::class]);
        $record = $table->get(1);
        $record->code = 'AUTRE_CODE';
        $this->assertFalse($table->save($record));
        $this->assertSame('Lorem ipsum do', $table->get(1)->code);

        $record = $table->get(1);
        $record->id = 99;
        $this->assertFalse($table->save($record));
        $this->assertTrue($table->exists(['id' => 1]));

        $local = $table->newEntity([
            'base' => false,
            'code' => 'ID_LOCAL',
            'name' => 'Identifiant local',
            'sort' => 'Identifiant local',
        ]);
        $this->assertNotFalse($table->save($local));
        $oldId = $local->id;
        $local->id = 99;
        $this->assertFalse($table->save($local));
        $this->assertTrue($table->exists(['id' => $oldId]));
    }

    /** La suppression physique et logique d'un socle persistant est refusée. */
    public function testSuppressionDuSocleRefuseeMalgreUneValeurEnMemoireModifiee(): void
    {
        $table = $this->getTableLocator()->get('Roles', ['className' => RolesTable::class]);
        $role = $table->get(1);
        $role->base = false;
        $this->assertFalse($table->delete($role));
        $this->assertTrue($table->exists(['id' => 1]));

        $role = $table->get(1);
        $role->base = false;
        $deleted = $role->deleted;
        $this->assertFalse($table->softDelete($role));
        $this->assertEquals($deleted, $table->get(1)->deleted);
    }

    /** Les lignes locales conservent leurs opérations courantes. */
    public function testCrudLocal(): void
    {
        $table = $this->getTableLocator()->get('Contracttypes', ['className' => ContracttypesTable::class]);
        $record = $table->newEntity([
            'base' => false,
            'code' => 'LOCAL',
            'name' => 'Local',
            'sort' => 'Local',
        ]);
        $this->assertNotFalse($table->save($record));
        $record->code = 'LOCAL_CHANGE';
        $record->name = 'Local modifié';
        $this->assertNotFalse($table->save($record));
        $this->assertSame('LOCAL_CHANGE', $table->get($record->id)->code);
        $this->assertTrue($table->delete($record));
    }

    /** Les départements utilisent la même protection ORM. */
    public function testDepartementSocleProtege(): void
    {
        $table = $this->getTableLocator()->get('Departments', ['className' => DepartmentsTable::class]);
        $record = $table->find()->where(['base' => true])->firstOrFail();
        $record->name = 'Département renommé';
        $this->assertFalse($table->save($record));
    }

    /** Les sept nomenclatures génériques appliquent toutes la protection. */
    public function testToutesLesNomenclaturesProtegentLeSocle(): void
    {
        $aliases = [
            'Budgetfeatures', 'Contracttypes', 'Hiringreasons', 'Periods',
            'Professionalcategories', 'Worktimes', 'Yesnos',
        ];
        foreach ($aliases as $alias) {
            $table = $this->getTableLocator()->get($alias);
            $record = $table->get(1);
            $record->name = 'Libellé modifié';
            $this->assertFalse($table->save($record), $alias);
        }
    }
}
