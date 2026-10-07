<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\UserMailer;
use App\Model\Entity\User;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Closure;

/**
 * Commande de test pour l'infrastructure d'envoi de courriels.
 * Permet de valider la configuration SMTP locale ou distante.
 */
class TestEmailCommand extends Command
{
    /**
     * @var \Closure(string): \App\Model\Entity\User
     */
    private readonly Closure $createUser;

    /**
     * @var \Closure(\App\Model\Entity\User): bool
     */
    private readonly Closure $sendEmail;

    /**
     * @param \Cake\Console\CommandFactoryInterface|null $factory Fabrique CakePHP.
     * @param \Closure(string): \App\Model\Entity\User|null $createUser Fabrique d’utilisateur injectable.
     * @param \Closure(\App\Model\Entity\User): bool|null $sendEmail Expéditeur injectable.
     */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
        ?Closure $createUser = null,
        ?Closure $sendEmail = null,
    ) {
        parent::__construct($factory);
        $this->createUser = $createUser ?? static function (string $email): User {
            $usersTable = TableRegistry::getTableLocator()->get('Users');

            return $usersTable->newEntity([
                'email' => $email,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'token' => 'TEST-TOKEN-123456789',
            ]);
        };
        $this->sendEmail = $sendEmail ?? static function ($user): bool {
            return (new UserMailer())->safeSend('forgotPassword', [$user]);
        };
    }

    /**
     * Configure les options et arguments de la commande.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Le parseur d'options.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->addArgument('email', [
            'help' => 'L\'adresse courriel de destination pour le test',
            'required' => true,
        ]);

        return $parser;
    }

    /**
     * Exécute la commande.
     *
     * @param \Cake\Console\Arguments $args Les arguments.
     * @param \Cake\Console\ConsoleIo $io L'interface d'Entrée/Sortie.
     * @return int|null Code de statut de la console.
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $email = $args->getArgument('email');
        $io->info("Préparation du courriel de test (forgotPassword) pour : {$email}");

        // Simulation d'une entité User (Skinny Controller / Command logic)
        $dummyUser = ($this->createUser)($email);

        // Utilisation de la méthode sécurisée de notre AppMailer
        if (($this->sendEmail)($dummyUser)) {
            $io->success('Succès : Le courriel a été accepté par le relais SMTP.');

            return static::CODE_SUCCESS;
        }

        $io->error('Échec : Le relais SMTP a rejeté l\'envoi. Consultez logs/email.log.');

        return static::CODE_ERROR;
    }
}
