<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * Class FieldAuthorizationsController (API)
 *
 * Exposition REST/JSON pour la gestion de la sécurité granulaire des champs.
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
     * @return void
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);

        // 💡 Instanciation explicite de la table et vérification d'autorisation
        $table = $this->fetchTable('FieldAuthorizations');
        $entity = $table->newEmptyEntity();
        
        // C'est cet appel qui valide la vérification auprès du middleware :
        $this->Authorization->authorize($entity, 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        // Requête avec chargement de l'association Roles
        $query = $table->find()->contain(['Roles']);
        $query = $adapter->adaptRequest($this->request, $query);

        $paginatedData = $this->paginate($query, [
            'limit' => (int)($queryParams['size'] ?? 20),
            'page' => (int)($queryParams['page'] ?? 1),
            'sortableFields' => ['id', 'resource', 'field', 'access_level', 'role_id'],
        ]);

        $output = $adapter->adaptResponse($paginatedData);

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

        $table = $this->fetchTable('FieldAuthorizations');
        $record = $table->newEmptyEntity();
        
        $this->Authorization->authorize($record, 'add');

        $record = $table->patchEntity($record, $this->request->getData());

        if ($table->save($record)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => __('Règle enregistrée avec succès.'),
                ]));
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode([
                'success' => false,
                'message' => __('Erreur lors de l\'enregistrement de la règle.'),
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

        $table = $this->fetchTable('FieldAuthorizations');
        $record = $table->get($id);

        $this->Authorization->authorize($record, 'edit');

        $record = $table->patchEntity($record, $this->request->getData());

        if ($table->save($record)) {
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

        $table = $this->fetchTable('FieldAuthorizations');
        $record = $table->get($id);

        $this->Authorization->authorize($record, 'delete');

        if ($table->delete($record)) {
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
     * @return void
     */
public function getResourcesAndFields(): void
    {
        $this->request->allowMethod(['get']);

        $table = $this->fetchTable('FieldAuthorizations');
        $this->Authorization->authorize($table->newEmptyEntity(), 'index');

        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $roles = $rolesTable->find('list', keyField: 'id', valueField: 'name')->toArray();

        // Introspection sécurisée table par table
        $resources = [];
        foreach (['Users', 'Applicationforms', 'Departments'] as $tableName) {
            try {
                $locator = TableRegistry::getTableLocator();
                if ($locator->exists($tableName) || class_exists("App\\Model\\Table\\{$tableName}Table")) {
                    $resources[$tableName] = $locator->get($tableName)->getSchema()->columns();
                }
            } catch (\Throwable $e) {
                // Ignore silencieusement si la table n'est pas instanciable
            }
        }

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
     * @param string $tableName
     * @return array<string>
     */
    private function getColumnsFromTable(string $tableName): array
    {
        try {
            $locator = TableRegistry::getTableLocator();
            if ($locator->exists($tableName) || class_exists("App\\Model\\Table\\{$tableName}Table")) {
                return $locator->get($tableName)->getSchema()->columns();
            }
        } catch (\Throwable $e) {
            // Silence en cas d'absence
        }

        return [];
    }
}