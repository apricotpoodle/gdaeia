<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * Class FieldAuthorizationsController (API)
 *
 * Exposition REST/JSON pour la gestion de la sécurité granulaire au niveau des champs.
 *
 * @package App\Controller\Api
 * @property \App\Model\Table\FieldAuthorizationsTable $FieldAuthorizations
 */
class FieldAuthorizationsController extends AppController
{
    /**
     * Initialisation du contrôleur API.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * Endpoint : GET /api/field-authorizations.json
     *
     * Distribue la liste des autorisations par champ pour la grille Tabulator.
     *
     * @return void
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);

        // Sécurité : Vérification via la Policy
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        $query = $this->FieldAuthorizations->find()
            ->contain(['Roles']);

        $query = $adapter->adaptRequest($this->request, $query);

        $paginatedData = $this->paginate($query, [
            'limit' => (int)($queryParams['size'] ?? 20),
            'page' => (int)($queryParams['page'] ?? 1),
            'sortableFields' => [],
        ]);

        $rightsFormatter = $this->createGridRightsFormatter();

        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /**
     * Endpoint : POST /api/field-authorizations/add.json
     *
     * @return \Cake\Http\Response
     */
    public function add(): Response
    {
        $this->request->allowMethod(['post']);

        $record = $this->FieldAuthorizations->newEmptyEntity();
        $this->Authorization->authorize($record, 'add');

        $record = $this->FieldAuthorizations->patchEntity($record, $this->request->getData());

        if ($this->FieldAuthorizations->save($record)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => __('Règle d\'autorisation enregistrée avec succès.'),
                ]));
        }

        $errors = $record->getErrors();
        $message = __("Erreur lors de l'enregistrement de la règle.");
        if (!empty($errors)) {
            $firstError = current(reset($errors));
            $message = (string)$firstError;
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode([
                'success' => false,
                'message' => $message,
            ]));
    }

    /**
     * Endpoint : PUT/POST /api/field-authorizations/edit/{id}.json
     *
     * @param string|null $id Identifiant de la règle.
     * @return \Cake\Http\Response
     */
    public function edit(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'put']);

        $record = $this->FieldAuthorizations->get($id);
        $this->Authorization->authorize($record, 'edit');

        $record = $this->FieldAuthorizations->patchEntity($record, $this->request->getData());

        if ($this->FieldAuthorizations->save($record)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => __('Règle mise à jour avec succès.'),
                ]));
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode([
                'success' => false,
                'message' => __('Impossible de mettre à jour la règle.'),
            ]));
    }

    /**
     * Endpoint : DELETE /api/field-authorizations/delete/{id}.json
     *
     * @param string|null $id Identifiant de la règle.
     * @return \Cake\Http\Response
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $record = $this->FieldAuthorizations->get($id);
        $this->Authorization->authorize($record, 'delete');

        if ($this->FieldAuthorizations->delete($record)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => __('Règle supprimée avec succès.'),
                ]));
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode([
                'success' => false,
                'message' => __('Impossible de supprimer la règle.'),
            ]));
    }

    /**
     * Endpoint : GET /api/field-authorizations/get-resources-and-fields.json
     *
     * Fournit les métadonnées pour hydrater les formulaires d'administration :
     * - Liste des Rôles
     * - Liste des Ressources (tables/modèles) et de leurs colonnes actives
     *
     * @return void
     */
    public function getResourcesAndFields(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'index');

        // 1. Liste des Rôles
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $roles = $rolesTable->find('list', keyField: 'id', valueField: 'name')->toArray();

        // 2. Mappage dynamique des ressources et de leurs colonnes
        $resources = [
            'Users' => $this->getColumnsFromTable('Users'),
            'Applicationforms' => $this->getColumnsFromTable('Applicationforms'),
            'Departments' => $this->getColumnsFromTable('Departments'),
        ];

        // 3. Niveaux d'accès valides
        $accessLevels = [
            'EDIT' => __('Édition (Complet)'),
            'VIEW' => __('Lecture Seule'),
            'NONE' => __('Masqué / Interdit'),
        ];

        $this->set(compact('roles', 'resources', 'accessLevels'));
        $this->viewBuilder()->setOption('serialize', ['roles', 'resources', 'accessLevels']);
    }

    /**
     * Extrait la liste des colonnes d'une table donnée.
     *
     * @param string $tableName Nom de la table ORM.
     * @return array<string>
     */
    private function getColumnsFromTable(string $tableName): array
    {
        try {
            $table = TableRegistry::getTableLocator()->get($tableName);
            return $table->getSchema()->columns();
        } catch (\Throwable $e) {
            return [];
        }
    }
}