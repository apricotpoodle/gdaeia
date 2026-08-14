<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Class FieldAuthorizationsController (Web)
 *
 * Contrôleur d'interface pour l'administration de la sécurité granulaire des champs.
 *
 * @package App\Controller
 * @property \App\Model\Table\FieldAuthorizationsTable $FieldAuthorizations
 */
class FieldAuthorizationsController extends AppController
{
    /**
     * Action Index (GET /field-authorizations)
     *
     * Renders templates/FieldAuthorizations/index.php.
     *
     * @return void
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'index');
    }
}