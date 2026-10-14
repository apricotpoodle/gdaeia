<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Convertit les anciennes valeurs de lecture seule vers le niveau canonique VIEW. */
final class NormalizeFieldAuthorizationAccessLevels extends BaseMigration
{
    /** Normalise les données historiques avant la suppression du support READONLY. */
    public function up(): void
    {
        $this->execute("UPDATE field_authorizations
            SET access_level = 'VIEW'
            WHERE UPPER(TRIM(access_level)) IN ('READONLY', 'READ_ONLY', 'READ-ONLY', 'READ ONLY')");
    }

    /** La conversion est irréversible car les variantes historiques sont fusionnées. */
    public function down(): void
    {
    }
}
