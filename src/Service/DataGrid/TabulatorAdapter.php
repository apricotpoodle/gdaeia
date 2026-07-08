<?php

declare(strict_types=1);

namespace App\Service\DataGrid;

use Authorization\AuthorizationServiceInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\Query\SelectQuery;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Utility\Inflector;
use Cake\Database\Expression\QueryExpression;

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

                // On autorise les chaînes non vides OU les tableaux (pour le filtre Date Range)
                if (is_string($field) && ($value !== '' || is_array($value))) {
                    $ormField = $this->resolveOrmField($field, $mainAlias);

                    // 🛡️ INTERCEPTION : Gestion spécifique du filtre "Date Range" (Plage de dates)
                    if (is_array($value) && (array_key_exists('start', $value) || array_key_exists('end', $value))) {
                        $this->applyDateRangeCondition($query, $ormField, $value);
                        continue; // On passe au filtre suivant
                    }

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
     * Applique les conditions de filtre SQL pour une plage de dates (Tabulator Date Range).
     * Formate intelligemment les limites horaires pour inclure la journée entière sur les champs DATETIME.
     * (Respect du principe DRY et encapsulation de la logique complexe).
     *
     * @param \Cake\ORM\Query\SelectQuery $query La requête ORM à modifier.
     * @param string $field Le champ ORM sécurisé (ex: 'Users.created').
     * @param array<string, mixed> $range Le tableau contenant les clés 'start' et/ou 'end'.
     * @return void
     */
    private function applyDateRangeCondition(SelectQuery $query, string $field, array $range): void
    {
        // Ajout des bornes horaires pour englober la journée entière (00:00:00 à 23:59:59)
        $start = !empty($range['start']) ? $range['start'] . ' 00:00:00' : null;
        $end   = !empty($range['end'])   ? $range['end'] . ' 23:59:59' : null;

        if ($start !== null && $end !== null) {
            // Condition stricte BETWEEN via QueryExpression (Compatible PHPStan)
            $query->where(function (QueryExpression $exp) use ($field, $start, $end) {
                return $exp->between($field, $start, $end);
            });
        } elseif ($start !== null) {
            $query->where(["{$field} >=" => $start]);
        } elseif ($end !== null) {
            $query->where(["{$field} <=" => $end]);
        }
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
     * Formate la réponse paginée pour Tabulator.
     *
     * @param \Cake\Datasource\Paging\PaginatedInterface $paginatedData Les entités issues de la pagination
     * @param callable|null $rightsFormatter Fonction anonyme pour injecter les grid_rights
     * @return array Structure JSON attendue par Tabulator
     */
    public function adaptResponse(PaginatedInterface $paginatedData, ?callable $rightsFormatter = null): array
    {
        // 1. CRUCIAL : On fige les entités dans un tableau physique (Array).
        // Cela empêche le moteur JSON de relancer un cycle d'itération
        // et de perdre nos droits dynamiques (grid_rights) calculés ci-dessous.
        $entities = [];
        foreach ($paginatedData->items() as $item) {
            $entities[] = $item;
        }

        // 2. Application des droits via la fabrique DRY du contrôleur
        if ($rightsFormatter !== null) {
            foreach ($entities as $entity) {
                $entity->grid_rights = $rightsFormatter($entity);
            }
        }

        // 3. Récupération des métadonnées de pagination (CakePHP 5)
        $pagingParams = $paginatedData->pagingParams();

        // 4. Formatage standard Tabulator
        return [
            'data' => $entities,
            'last_page' => $pagingParams['pageCount'] ?? 1, // Restauration de last_page !
        ];
    }
}
