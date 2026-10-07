<?php
declare(strict_types=1);

namespace App\Controller;

/** Contrôleur des catégories professionnelles. */
final class ProfessionalcategoriesController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Professionalcategories';
    }

    /** @return string Libellé affiché. */
    protected function referenceLabel(): string
    {
        return __('Catégories professionnelles');
    }
}
