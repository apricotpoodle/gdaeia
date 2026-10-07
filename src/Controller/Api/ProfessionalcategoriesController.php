<?php
declare(strict_types=1);

namespace App\Controller\Api;

/** API des catégories professionnelles. */
/**
 * @property \App\Model\Table\ProfessionalcategoriesTable $Professionalcategories
 */
final class ProfessionalcategoriesController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Professionalcategories';
    }
}
