<?php
declare(strict_types=1);

namespace App\Controller;

class FieldAuthorizationsController extends AppController
{
    public function index(): void
    {
        // Instanciation explicite pour passer le middleware d'autorisation
        $fieldAuthorization = $this->fetchTable('FieldAuthorizations')->newEmptyEntity();
        $this->Authorization->authorize($fieldAuthorization, 'index');
    }
}