<?php

declare(strict_types=1);

namespace App\Service\DataGrid;

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
     * Applique les paramètres de tri (Sorters) et de filtrage (Filters)
     * envoyés par Tabulator à la requête ORM.
     *
     * @param \Cake\Http\ServerRequest $request L'objet requête HTTP courant.
     * @param \Cake\ORM\Query\SelectQuery $query La requête ORM initiale.
     * @return \Cake\ORM\Query\SelectQuery La requête ORM modifiée.
     */
    public function adaptRequest(ServerRequest $request, SelectQuery $query): SelectQuery
    {
        $queryParams = $request->getQueryParams();
        $mainAlias = $query->getRepository()->getAlias();

        // --- 1. GESTION DES TRIS (Sorters) ---
        if (!empty($queryParams['sorters']) && is_array($queryParams['sorters'])) {
            foreach ($queryParams['sorters'] as $sorter) {
                $field = $sorter['field'] ?? null;
                $direction = strtoupper($sorter['dir'] ?? 'ASC');

                if (is_string($field) && in_array($direction, ['ASC', 'DESC'], true)) {
                    $ormField = $this->resolveOrmField($field, $mainAlias);
                    $query->orderBy([$ormField => $direction]);
                }
            }
        }

        // --- 2. GESTION DES FILTRES (Filters) ---
        if (!empty($queryParams['filters']) && is_array($queryParams['filters'])) {
            foreach ($queryParams['filters'] as $filter) {
                $field = $filter['field'] ?? null;
                $type = strtolower($filter['type'] ?? '=');
                $value = $filter['value'] ?? '';

                if (is_string($field) && $value !== '') {
                    $ormField = $this->resolveOrmField($field, $mainAlias);

                    // SÉCURITÉ & NORMALISATION DES TYPES numérique (ID)
                    // Si on filtre sur l'ID, on force une égalité stricte, peu importe
                    // ce que demande le front.
                    //
                    // Conserver ce test même si c'est censé être traité dans le front end
                    // Car on peut craindre un petit malin modifiant l'url = en like
                    if (strtolower($field) === 'id') {
                        $query->where([$ormField => (int)$value]);
                        continue; // On passe au filtre suivant
                    }

                    // Traduction de l'opérateur Tabulator vers la clause ORM
                    switch ($type) {
                        case 'like':
                            $query->where([$ormField . ' LIKE' => '%' . $value . '%']);
                            break;
                        case '=':
                        case '!=':
                        case '<':
                        case '<=':
                        case '>':
                        case '>=':
                            $query->where([$ormField . ' ' . $type => $value]);
                            break;
                        default:
                            $query->where([$ormField => $value]);
                    }
                }
            }
        }

        return $query;
    }

    /**
     * Résout l'ambiguïté des noms de colonnes entre le format JSON et l'ORM SQL.
     * (Méthode privée extraite pour respecter le principe DRY)
     *
     * @param string $field Le champ envoyé par Tabulator (ex: "id" ou "role.name")
     * @param string $mainAlias L'alias de la table principale
     * @return string Le champ sécurisé pour l'ORM (ex: "Users.id" ou "Roles.name")
     */
    private function resolveOrmField(string $field, string $mainAlias): string
    {
        if (strpos($field, '.') === false) {
            return $mainAlias . '.' . $field;
        }

        [$relation, $column] = explode('.', $field, 2);
        $relationAlias = Inflector::camelize(Inflector::pluralize($relation));
        return $relationAlias . '.' . $column;
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
