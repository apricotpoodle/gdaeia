<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Répare l’index unique du catalogue CGR après la normalisation des départements. */
final class RepairCgrCodesUniqueIndex extends BaseMigration
{
    public function up(): void
    {
        if (!$this->hasTable('cgr_codes')) {
            return;
        }

        $cgrCodes = $this->table('cgr_codes');
        if ($this->hasIndex('cgr_codes', 'idx_cgr_codes_unique_definition')) {
            $cgrCodes->removeIndexByName('idx_cgr_codes_unique_definition');
        }
        $cgrCodes->addIndex(['type', 'code'], [
            'name' => 'idx_cgr_codes_unique_definition',
            'unique' => true,
        ])->update();
    }

    public function down(): void
    {
        if ($this->hasIndex('cgr_codes', 'idx_cgr_codes_unique_definition')) {
            $this->table('cgr_codes')
                ->removeIndexByName('idx_cgr_codes_unique_definition')
                ->update();
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return $this->query(sprintf(
            "SHOW INDEX FROM `%s` WHERE Key_name = '%s'",
            $table,
            $index,
        ))->fetch() !== false;
    }
}
