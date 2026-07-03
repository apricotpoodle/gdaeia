<?php
declare(strict_types=1);

namespace App\Service\DataGrid;

use Cake\Http\ServerRequest;
use Cake\ORM\Query\SelectQuery;
use Cake\Datasource\Paging\PaginatedInterface;

/**
 * Class TabulatorAdapter
 *
 * Design Pattern: Adapter (GoF - Bande des Quatre).
 * * Cette classe a pour unique responsabilité (SOLID: SRP) d'agir comme un pont
 * entre le format de données imposé par la librairie front-end Tabulator
 * et l'ORM natif de CakePHP.
 *
 * @package App\Service\DataGrid
 */
class TabulatorAdapter
{
    /**
     * Adapte les paramètres de tri et de filtre de Tabulator pour l'ORM CakePHP.
     * * Tabulator envoie les paramètres de tri sous ce format JSON :
     * `sorters[0][field]=name&sorters[0][dir]=asc`
     *
     * @param ServerRequest $request L'objet requête HTTP courant de CakePHP.
     * @param SelectQuery $query La requête ORM initiale non modifiée.
     * @return SelectQuery La requête ORM enrichie avec les clauses ORDER BY.
     */
    public function adaptRequest(ServerRequest $request, SelectQuery $query): SelectQuery
    {
        $queryParams = $request->getQueryParams();

        if (empty($queryParams['sorters']) || !is_array($queryParams['sorters'])) {
            return $query;
        }

        foreach ($queryParams['sorters'] as $sorter) {
            $field = $sorter['field'] ?? null;
            $direction = strtoupper($sorter['dir'] ?? 'ASC');
            
            // Sécurité : On valide la direction pour éviter les injections SQL basiques
            if (is_string($field) && in_array($direction, ['ASC', 'DESC'], true)) {
                $query->order([$field => $direction]);
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
        // Récupération du tableau des paramètres de pagination (CakePHP 5)
        $pagingParams = $paginated->pagingParams();

        return [
            'last_page' => $pagingParams['pageCount'] ?? 1,
            // iterator_to_array garantit la compatibilité stricte avec PaginatedInterface
            'data'      => iterator_to_array($paginated),
        ];
    }
}