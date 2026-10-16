<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Integrity;

use App\Service\Integrity\ReferenceCatalog;
use Cake\TestSuite\TestCase;

/** Vérifie le catalogue explicite des références contrôlées. */
class ReferenceCatalogTest extends TestCase
{
    public function testExposeLesReferencesDuWorkflowAvecLeurPolitiqueDeSoftDelete(): void
    {
        $catalog = new ReferenceCatalog();

        $definition = $catalog->get('applicationvalidationsteps.validationsequence_id');

        $this->assertSame('validationsequences', $definition->targetTable);
        $this->assertSame('historical', $definition->targetPolicy);
        $this->assertFalse($definition->nullable);
    }

    public function testInclutLesAssociationsORMEtExclutLesIdentifiantsExternes(): void
    {
        $catalog = new ReferenceCatalog();
        $definitions = $catalog->all();

        $this->assertArrayHasKey('applicationforms.department_id', $definitions);
        $this->assertArrayHasKey('role_menus.menu_id', $definitions);
        $this->assertArrayHasKey('comments.parent_id', $definitions);
        $this->assertArrayNotHasKey('applicationforms.collaborator_id', $definitions);
    }

    public function testLesIdentifiantsDeReferencesSontUniques(): void
    {
        $catalog = new ReferenceCatalog();

        $this->assertCount(count($catalog->all()), array_unique(array_keys($catalog->all())));
    }
}
