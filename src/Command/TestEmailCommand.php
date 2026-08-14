<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\UserMailer;
use Cake\COmmand\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;

/**
 * Commande de test pour l'infrastructure d'envoi de courriels.
 * Permet de valider la configuration SMTP locale ou distante.
 */
class TestEmailCommand extends Command
{
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
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $dummyUser = $usersTable->newEntity([
            'email' => $email,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'token' => 'TEST-TOKEN-123456789'
        ]);

        $mailer = new UserMailer();
        
        // Utilisation de la méthode sécurisée de notre AppMailer
        if ($mailer->safeSend('forgotPassword', [$dummyUser])) {
            $io->success('Succès : Le courriel a été accepté par le relais SMTP.');
            return static::CODE_SUCCESS;
        }

        $io->error('Échec : Le relais SMTP a rejeté l\'envoi. Consultez logs/email.log.');
        return static::CODE_ERROR;
    }
}