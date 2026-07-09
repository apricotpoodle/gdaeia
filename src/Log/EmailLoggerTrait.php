<?php

declare(strict_types=1);

namespace App\Log;

/**
 * Trait EmailLoggerTrait
 * * Fournit une méthode raccourcie et percutante pour expédier des logs
 * directement dans le scope 'email' (logs/email.log).
 * Peut être utilisé dans les Contrôleurs, les Mailers, les Commandes, etc.
 * * @package App\Log
 */
trait EmailLoggerTrait
{
    /**
     * Trace une activité liée aux courriels dans le canal dédié.
     *
     * @param string $message Le message à journaliser.
     * @param string $level Le niveau de sévérité (info, error, warning, debug...).
     * @return void
     */
    protected function traceEmail(string $message, string $level = 'info'): void
    {
        // On s'appuie sur la méthode log() native de CakePHP qui est
        // généralement incluse via Cake\Log\LogTrait dans la plupart des classes de base.
        //
        // Utilisation de la façade statique au lieu de $this->log()
        // Évite l'erreur "Call to undefined method" dans les classes
        // qui n'incluent pas le LogTrait natif de CakePHP (comme le Mailer).
        // L'antislash (\) initial est crucial : il force PHP à chercher la façade Log
        // dans le noyau de CakePHP, ignorant le namespace courant (App\Log).
        \Cake\Log\Log::write($level, $message, ['scope' => ['email']]);
    }
}
