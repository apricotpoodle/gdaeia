<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\ServerRequest;
use Cake\ORM\Query\SelectQuery;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Utility\Inflector; 

/**
 * Class TabulatorAdapter
 *
 * Implémentation du Patron de Conception "Adaptateur" (GoF).
 * Cette classe a pour unique responsabilité de traduire les requêtes de la grille
 * front-end (Tabulator) vers l'ORM CakePHP, et de formater la réponse paginée.
 *
 * @package App\Service
 */
class TabulatorAdapter
{
    /**
     * Applique les paramètres de tri (Sorters) envoyés par Tabulator à la requête ORM.
     * Résout automatiquement les ambiguïtés SQL et traduit les relations.
     *
     * @param \Cake\Http\ServerRequest $request L'objet requête HTTP courant.
     * @param \Cake\ORM\Query\SelectQuery $query La requête ORM initiale.
     * @return \Cake\ORM\Query\SelectQuery La requête ORM modifiée avec les clauses ORDER BY.
     */
    public function adaptRequest(ServerRequest $request, SelectQuery $query): SelectQuery
    {
        $queryParams = $request->getQueryParams();
        // Récupère l'alias de la table principale (ex: 'Users')
        $mainAlias = $query->getRepository()->getAlias(); 

        if (empty($queryParams['sorters']) || !is_array($queryParams['sorters'])) {
            return $query;
        }

        foreach ($queryParams['sorters'] as $sorter) {
            $field = $sorter['field'] ?? null;
            $direction = strtoupper($sorter['dir'] ?? 'ASC');
            
            if (is_string($field) && in_array($direction, ['ASC', 'DESC'], true)) {
                
                // Si le champ ne contient pas de point (ex: "id"), on préfixe avec la table principale ("Users.id")
                if (strpos($field, '.') === false) {
                    $ormField = $mainAlias . '.' . $field;
                } else {
                    // S'il contient un point (ex: "role.name"), on traduit la notation JSON en notation ORM ("Roles.name")
                    [$relation, $column] = explode('.', $field, 2);
                    $relationAlias = Inflector::camelize(Inflector::pluralize($relation));
                    $ormField = $relationAlias . '.' . $column;
                }
                
                $query->order([$ormField => $direction]);
            }
        }

        return $query;
    }

    /**
     * Formate le résultat de la pagination CakePHP au format JSON strict attendu par Tabulator.
     *
     * @param \Cake\Datasource\Paging\PaginatedInterface $paginated L'objet paginé issu de CakePHP.
     * @return array{last_page: int, data: array} Le tableau associatif prêt à être sérialisé en JSON.
     */
    public function adaptResponse(PaginatedInterface $paginated): array
    {
        $pagingParams = $paginated->pagingParams();

        return [
            'last_page' => $pagingParams['pageCount'] ?? 1,
            'data'      => iterator_to_array($paginated),
        ];
    }
}