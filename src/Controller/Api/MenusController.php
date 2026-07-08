<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Event\EventInterface;

/**
 * @class MenusController
 * @description Contrôleur d'API distribuant l'arborescence complète du menu pour le Front-End.
 * @property \App\Model\Table\MenusTable $Menus
 */
class MenusController extends AppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // Sécurité (ADR 0026) : Autorise la lecture du menu pour les profils connectés
        $this->Authorization->skipAuthorization(['index']);
    }

    /**
     * Action Index : GET /api/menus.json
     * Extrait la trinité de l'arbre ordonnée de manière fluide.
     *
     * @return void
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);

        // Extraction brute et structuration en arbre native par l'ORM (`children`)
        $menus = $this->Menus->find('threaded')
            ->where(['active' => true])
            ->orderBy(['lft' => 'ASC'])
            ->all();

        $this->set(compact('menus'));
        $this->viewBuilder()->setOption('serialize', ['menus']);
    }
}
