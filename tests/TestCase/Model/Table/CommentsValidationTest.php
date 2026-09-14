<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CommentsTable;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use ReflectionClass;

class CommentsValidationTest extends TestCase
{
    private CommentsTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var CommentsTable $table */
        $table = (new ReflectionClass(CommentsTable::class))->newInstanceWithoutConstructor();
        $this->table = $table;
    }

    public function testCommentCreationRejectsEmptyTargetContentAndAuthor(): void
    {
        $errors = $this->validator()->validate([
            'model' => null,
            'foreign_key' => null,
            'type' => null,
            'content' => null,
            'user_id' => null,
        ], true);

        foreach (['model', 'foreign_key', 'type', 'content', 'user_id'] as $field) {
            $this->assertArrayHasKey($field, $errors);
        }
    }

    public function testNegativeIdentifiersAndOversizedModelAreRejected(): void
    {
        $errors = $this->validator()->validate([
            'parent_id' => -1,
            'foreign_key' => -1,
            'user_id' => -1,
            'model' => str_repeat('a', 65),
        ], true);

        $this->assertArrayHasKey('parent_id', $errors);
        $this->assertArrayHasKey('foreign_key', $errors);
        $this->assertArrayHasKey('user_id', $errors);
        $this->assertArrayHasKey('model', $errors);
    }

    private function validator(): Validator
    {
        return $this->table->validationDefault(new Validator());
    }
}
