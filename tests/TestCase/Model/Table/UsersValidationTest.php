<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use ReflectionClass;

class UsersValidationTest extends TestCase
{
    private UsersTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var UsersTable $table */
        $table = (new ReflectionClass(UsersTable::class))->newInstanceWithoutConstructor();
        $this->table = $table;
    }

    public function testUserCreationRejectsEmptyEmailPasswordRoleAndSuperuserFlag(): void
    {
        $errors = $this->validator()->validate([
            'email' => null,
            'password' => null,
            'issuperuser' => null,
            'role_id' => null,
        ], true);

        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertArrayHasKey('issuperuser', $errors);
        $this->assertArrayHasKey('role_id', $errors);
    }

    public function testInvalidEmailAndNonBooleanSuperuserFlagAreRejected(): void
    {
        $errors = $this->validator()->validate([
            'email' => 'not-an-email',
            'issuperuser' => 'administrator',
        ], true);

        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('issuperuser', $errors);
    }

    private function validator(): Validator
    {
        return $this->table->validationDefault(new Validator());
    }
}
