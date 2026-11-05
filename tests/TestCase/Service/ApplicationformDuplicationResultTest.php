<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Applicationform;
use App\Service\ApplicationformDuplicationResult;
use Cake\TestSuite\TestCase;

final class ApplicationformDuplicationResultTest extends TestCase
{
    public function testUnSuccesExposeLaCopie(): void
    {
        $applicationform = new Applicationform(['id' => 42]);

        $result = ApplicationformDuplicationResult::success($applicationform);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($applicationform, $result->applicationform);
        $this->assertNull($result->category);
        $this->assertSame([], $result->errors);
    }

    public function testUnEchecExposeUneCategorieUnMessageEtDesErreurs(): void
    {
        $errors = [['field' => 'jobtitle', 'label' => 'Intitulé', 'reason' => 'Ce champ est obligatoire.']];

        $result = ApplicationformDuplicationResult::failure(
            ApplicationformDuplicationResult::CATEGORY_VALIDATION,
            'La duplication doit être corrigée.',
            $errors,
        );

        $this->assertFalse($result->isSuccess());
        $this->assertNull($result->applicationform);
        $this->assertSame(ApplicationformDuplicationResult::CATEGORY_VALIDATION, $result->category);
        $this->assertSame('La duplication doit être corrigée.', $result->message);
        $this->assertSame($errors, $result->errors);
    }

    public function testLesCategoriesDeBlocageSontStables(): void
    {
        $this->assertSame('reference', ApplicationformDuplicationResult::CATEGORY_REFERENCE);
        $this->assertSame('persistence', ApplicationformDuplicationResult::CATEGORY_PERSISTENCE);
    }
}
