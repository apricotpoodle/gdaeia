<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\ValidationAcceptedDigestCommand;
use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Model\Entity\WorkflowSetting;
use Cake\Console\Arguments;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Closure;

/** Vérifie la période ISO et les reprises de la synthèse hebdomadaire. */
class ValidationAcceptedDigestCommandTest extends CommandTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $query = $this->createStub(SelectQuery::class);
        $query->method('where')->willReturnSelf();
        $query->method('first')->willReturn(new WorkflowSetting([
            'value' => DateTime::now()->format('o-W'),
        ]));
        $settings = $this->createStub(Table::class);
        $settings->method('find')->willReturn($query);
        TableRegistry::getTableLocator()->remove('WorkflowSettings');
        TableRegistry::getTableLocator()->set('WorkflowSettings', $settings);
    }

    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('WorkflowSettings');
        parent::tearDown();
    }

    public function testUtiliseLaSemaineIsoEtReprendUniquementLeDestinataireEnEchec(): void
    {
        $first = new User(['id' => 1, 'email' => 'premier@example.test']);
        $second = new User(['id' => 2, 'email' => 'second@example.test']);
        $form = new Applicationform(['id' => 42]);
        $periods = [];
        $collect = static function (DateTime $from, DateTime $to) use (&$periods, $first, $second, $form): array {
            $periods[] = [$from, $to];

            return [
                ['recipient' => $first, 'applicationforms' => [$form]],
                ['recipient' => $second, 'applicationforms' => [$form]],
            ];
        };
        $attempts = [];
        $confirmed = [];
        $send = static function (User $recipient) use (&$attempts): bool {
            $email = strtolower($recipient->email);
            $attempts[$email] = ($attempts[$email] ?? 0) + 1;

            return $email !== 'second@example.test' || $attempts[$email] > 1;
        };
        $deliveries = [];
        $sendOnce = static function (string $type, string $eventKey, User $recipient, Closure $send)
 use (&$confirmed, &$deliveries): bool {
                $deliveries[] = [$type, $eventKey];
                $key = $type . ':' . $eventKey . ':' . strtolower(trim($recipient->email));
            if (isset($confirmed[$key])) {
                return true;
            }
            if (!$send()) {
                return false;
            }
                $confirmed[$key] = true;

                return true;
        };
        $command = new ValidationAcceptedDigestCommand(null, $collect, $send, $sendOnce);
        $firstStreams = $this->createIo();

        $firstResult = $command->execute(new Arguments([], [], []), $firstStreams['io']);
        $this->assertSame(ValidationAcceptedDigestCommand::CODE_ERROR, $firstResult);

        $secondStreams = $this->createIo();
        $secondResult = $command->execute(new Arguments([], [], []), $secondStreams['io']);

        $this->assertSame(ValidationAcceptedDigestCommand::CODE_SUCCESS, $secondResult);
        $this->assertSame([
            'premier@example.test' => 1,
            'second@example.test' => 2,
        ], $attempts);
        $this->assertNotEmpty($periods);
        foreach ($periods as [$from, $to]) {
            $this->assertSame('1', $from->format('N'));
            $this->assertSame('00:00:00', $from->format('H:i:s'));
            $this->assertEquals($from->addDays(7), $to);
        }
        $this->assertSame(['accepted_digest'], array_values(array_unique(array_column($deliveries, 0))));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $deliveries[0][1]);
        $this->assertStringNotContainsString('example.test', $this->streamContents($secondStreams['out']));
    }

    public function testAucuneDemandeAccepteeNeCreePasDeLivraison(): void
    {
        $sent = 0;
        $command = new ValidationAcceptedDigestCommand(
            null,
            static fn(DateTime $from, DateTime $to): array => [],
            static function () use (&$sent): bool {
                $sent++;

                return true;
            },
            static fn(string $type, string $eventKey, User $recipient, Closure $send): bool => $send(),
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(ValidationAcceptedDigestCommand::CODE_SUCCESS, $result);
        $this->assertSame(0, $sent);
    }
}
