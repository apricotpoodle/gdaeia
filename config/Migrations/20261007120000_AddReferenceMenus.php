<?php
declare(strict_types=1);

use Cake\ORM\TableRegistry;
use Migrations\BaseMigration;

/** Ajoute la rubrique et les options de menu des nomenclatures métier. */
final class AddReferenceMenus extends BaseMigration
{
    /** @var array<string, string> */
    private array $references = [
        'contracttypes' => 'Types de contrats',
        'hiringreasons' => 'Motifs de recrutement',
        'professionalcategories' => 'Catégories professionnelles',
        'worktimes' => 'Temps de travail',
        'periods' => 'Périodicités',
        'budgetfeatures' => 'Caractéristiques budgétaires',
        'yesnos' => 'Réponses Oui / Non',
    ];

    public function up(): void
    {
        $menus = TableRegistry::getTableLocator()->get('Menus');
        $parent = $menus->find()->where(['name' => 'Références', 'parent_id IS' => null])->first();

        if ($parent === null) {
            $parent = $menus->newEntity([
                'name' => 'Références',
                'url' => null,
                'active' => true,
                'disabled' => false,
                'dividor_before' => false,
            ]);
            $menus->saveOrFail($parent);
        }

        foreach ($this->references as $slug => $label) {
            $url = '/' . $slug;
            $exists = $menus->find()->where(['url' => $url])->first();
            if ($exists !== null) {
                continue;
            }

            $menu = $menus->newEntity([
                'parent_id' => $parent->id,
                'name' => $label,
                'url' => $url,
                'active' => true,
                'disabled' => false,
                'dividor_before' => false,
            ]);
            $menus->saveOrFail($menu);
        }
    }

    public function down(): void
    {
        $menus = TableRegistry::getTableLocator()->get('Menus');
        $urls = array_map(static fn (string $slug): string => '/' . $slug, array_keys($this->references));
        foreach ($urls as $url) {
            $menu = $menus->find()->where(['url' => $url])->first();
            if ($menu !== null) {
                $menus->delete($menu);
            }
        }
        $parent = $menus->find()->where(['name' => 'Références', 'parent_id IS' => null])->first();
        if ($parent !== null && $menus->find()->where(['parent_id' => $parent->id])->count() === 0) {
            $menus->delete($parent);
        }
    }
}
