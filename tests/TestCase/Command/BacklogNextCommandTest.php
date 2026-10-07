<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\BacklogNextCommand;
use Cake\Console\Arguments;

/** Vérifie la sélection du prochain ticket du backlog. */
class BacklogNextCommandTest extends CommandTestCase
{
    public function testAfficheLePremierTicketDontLesDependancesSontTerminees(): void
    {
        $streams = $this->createIo();
        $command = new BacklogNextCommand();

        $result = $command->execute(
            new Arguments(['validation-af'], [], ['workflow']),
            $streams['io'],
        );

        $this->assertSame(BacklogNextCommand::CODE_SUCCESS, $result);
        $this->assertStringContainsString('0021 — 0021-concurrence-votes-meme-role.md', $this->streamContents($streams['out']));
    }

    public function testRefuseUnWorkflowInconnu(): void
    {
        $streams = $this->createIo();
        $command = new BacklogNextCommand();

        $result = $command->execute(
            new Arguments(['workflow-inconnu'], [], ['workflow']),
            $streams['io'],
        );

        $this->assertSame(BacklogNextCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('Workflow inconnu.', $this->streamContents($streams['err']));
    }
}
