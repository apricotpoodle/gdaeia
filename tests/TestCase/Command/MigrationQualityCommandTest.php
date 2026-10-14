<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\MigrationQualityCommand;
use Cake\Console\Arguments;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MigrationQualityCommand::class)]
final class MigrationQualityCommandTest extends CommandTestCase
{
    public function testControleLesDoublonsEtLesLiensOrphelins(): void
    {
        $directory = $this->createCsvDirectory([
            '01-users.csv' => "source_user_id,email,source_username\n1,dup@example.test,un\n2,dup@example.test,deux\n3,,sans\n",
            '02-roles.csv' => "source_role_id,source_role_code\n10,admin\n",
            '03-user-roles.csv' => "source_user_id,source_role_id,source_company_id\n1,10,1\n99,10,1\n1,88,1\n",
        ]);
        $streams = $this->createIo();

        $result = (new MigrationQualityCommand())->execute(
            new Arguments([$directory], [], ['directory']),
            $streams['io'],
        );

        $this->assertSame(MigrationQualityCommand::CODE_SUCCESS, $result);
        $this->assertStringContainsString('3 (1 importables, 1 sans courriel, 1 courriels dupliqués).', $this->streamContents($streams['out']));
        $this->assertStringContainsString('liens utilisateurs-rôles orphelins : 2.', $this->streamContents($streams['out']));
    }

    public function testRefuseUneDonneeSensible(): void
    {
        $directory = $this->createCsvDirectory([
            '01-users.csv' => "source_user_id,email,USR_PWD\n1,user@example.test,secret\n",
            '02-roles.csv' => "source_role_id,source_role_code\n10,admin\n",
            '03-user-roles.csv' => "source_user_id,source_role_id\n1,10\n",
        ]);
        $streams = $this->createIo();

        $result = (new MigrationQualityCommand())->execute(
            new Arguments([$directory], [], ['directory']),
            $streams['io'],
        );

        $this->assertSame(MigrationQualityCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('Donnée sensible détectée', $this->streamContents($streams['err']));
    }

    /** @param array<string, string> $files */
    private function createCsvDirectory(array $files): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gdaetf2-migration-' . bin2hex(random_bytes(4));
        mkdir($directory, 0770, true);
        foreach ($files as $name => $contents) {
            file_put_contents($directory . DIRECTORY_SEPARATOR . $name, $contents);
        }

        return $directory;
    }
}
