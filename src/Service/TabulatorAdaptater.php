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
     * Formate le résultat paginé de CakePHP dans le formalisme JSON attendu par Tabulator.
     *
     * Tabulator attend un objet JSON structuré avec la clé `data` (le tableau des résultats)
     * et `last_page` (le nombre total de pages) pour le mode "remote pagination".
     *
     * @param PaginatedInterface $paginated Le résultat paginé fourni par CakePHP.
     * @return array<string, mixed> Le tableau associatif prêt à être encodé en JSON.
     */
    public function adaptResponse(PaginatedInterface $paginated): array
    {
        return [
            'last_page' => $paginated->getPagingParam('pageCount') ?? 1,
            'data'      => $paginated->toArray(),
        ];
    }
}