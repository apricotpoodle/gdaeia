<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Locator\LocatorInterface;
use Cake\ORM\Table;
use InvalidArgumentException;

/**
 * Vérifie la cohérence des arbres intervallaires gérés par TreeBehavior.
 *
 * La reconstruction n'est volontairement pas effectuée ici. Ce diagnostic est
 * conçu pour identifier notamment les graphes parent_id que recover() ne peut
 * pas réparer (cycles et parents inexistants).
 */
final class TreeIntegrityChecker
{
    /** @var array<string, string> Nom CLI => alias ORM */
    private const TREE_TABLES = [
        'departments' => 'Departments',
        'menus' => 'Menus',
    ];

    /**
     * @param \Cake\ORM\Locator\LocatorInterface $tableLocator Registre ORM à interroger.
     */
    public function __construct(private readonly LocatorInterface $tableLocator)
    {
    }

    /** @return list<string> */
    public static function tableNames(): array
    {
        return array_keys(self::TREE_TABLES);
    }

    /** @return list<array<string, mixed>> */
    public function check(?string $tableName = null): array
    {
        if ($tableName !== null && !isset(self::TREE_TABLES[$tableName])) {
            throw new InvalidArgumentException(__(
                'Table d’arbre inconnue : {0}. Tables acceptées : {1}.',
                $tableName,
                implode(', ', self::tableNames()),
            ));
        }

        $tables = $tableName === null ? self::TREE_TABLES : [$tableName => self::TREE_TABLES[$tableName]];
        $reports = [];
        foreach ($tables as $name => $alias) {
            $reports[] = $this->checkTable($name, $this->tableLocator->get($alias));
        }

        return $reports;
    }

    /** @return array<string, mixed> */
    private function checkTable(string $name, Table $table): array
    {
        $issues = [];
        if (!$table->behaviors()->has('Tree')) {
            $this->addIssue($issues, 'CONFIGURATION', 'Le behavior Tree n’est pas chargé.', []);

            return $this->report($name, $table, 0, $issues);
        }

        /** @var \Cake\ORM\Behavior\TreeBehavior $tree */
        $tree = $table->getBehavior('Tree');
        $parentField = (string)$tree->getConfig('parent');
        $leftField = (string)$tree->getConfig('left');
        $rightField = (string)$tree->getConfig('right');
        $levelField = $tree->getConfig('level');
        $primaryKey = $table->getPrimaryKey();
        if (!is_string($primaryKey)) {
            $this->addIssue(
                $issues,
                'CONFIGURATION',
                'TreeBehavior ne prend pas en charge les clés primaires composites.',
                [],
            );

            return $this->report($name, $table, 0, $issues);
        }

        $fields = array_filter([
            $primaryKey,
            $parentField,
            $leftField,
            $rightField,
            is_string($levelField) ? $levelField : null,
        ]);
        foreach ($fields as $field) {
            if (!$table->getSchema()->hasColumn($field)) {
                $this->addIssue($issues, 'CONFIGURATION', 'Colonne requise absente du schéma.', ['column' => $field]);
            }
        }
        if ($issues !== []) {
            return $this->report($name, $table, 0, $issues);
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $table->find()->select($fields)->enableHydration(false)->all()->toList();
        $nodes = [];
        foreach ($rows as $row) {
            $id = $row[$primaryKey];
            $nodes[(string)$id] = [
                'id' => $id,
                'parent_id' => $row[$parentField],
                'lft' => $row[$leftField] === null ? null : (int)$row[$leftField],
                'rght' => $row[$rightField] === null ? null : (int)$row[$rightField],
                'level' => is_string($levelField) && $row[$levelField] !== null ? (int)$row[$levelField] : null,
            ];
        }

        $this->checkGraph($nodes, $issues);
        $this->checkIntervals($nodes, $issues);
        if (is_string($levelField)) {
            $this->checkLevels($nodes, $issues);
        }
        $this->checkDescendantCounts($nodes, $issues);

        return $this->report($name, $table, count($nodes), $issues);
    }

    /** @param array<string, array<string, mixed>> $nodes @param list<array<string, mixed>> $issues */
    private function checkGraph(array $nodes, array &$issues): void
    {
        foreach ($nodes as $key => $node) {
            $parent = $node['parent_id'];
            if ($parent === null) {
                continue;
            }
            $parentKey = (string)$parent;
            if ($parentKey === (string)$key) {
                $this->addIssue($issues, 'PARENT_LUI_MEME', 'Un nœud est son propre parent.', ['node' => $node]);
            } elseif (!isset($nodes[$parentKey])) {
                $this->addIssue($issues, 'PARENT_INEXISTANT', 'Le parent référencé n’existe pas.', [
                    'node' => $node,
                    'missing_parent_id' => $parent,
                ]);
            }
        }

        $reportedCycles = [];
        foreach (array_keys($nodes) as $start) {
            $path = [];
            $positions = [];
            $current = $start;
            while (isset($nodes[$current]) && $nodes[$current]['parent_id'] !== null) {
                if (isset($positions[$current])) {
                    $cycle = array_slice($path, $positions[$current]);
                    $cycle[] = $current;
                    $key = $cycle;
                    sort($key, SORT_STRING);
                    $key = implode('|', $key);
                    if (!isset($reportedCycles[$key])) {
                        $reportedCycles[$key] = true;
                        $this->addIssue(
                            $issues,
                            'CYCLE_PARENT',
                            'Cycle détecté dans la chaîne parent_id.',
                            ['path' => $cycle],
                        );
                    }
                    break;
                }
                $positions[$current] = count($path);
                $path[] = $current;
                $current = (string)$nodes[$current]['parent_id'];
            }
        }
    }

    /** @param array<string, array<string, mixed>> $nodes @param list<array<string, mixed>> $issues */
    private function checkIntervals(array $nodes, array &$issues): void
    {
        $boundaries = [];
        $validNodes = [];
        foreach ($nodes as $key => $node) {
            $left = $node['lft'];
            $right = $node['rght'];
            if (!is_int($left) || !is_int($right)) {
                $this->addIssue($issues, 'BORNE_NULLE', 'Une borne lft ou rght est nulle.', ['node' => $node]);
                continue;
            }
            if ($left < 1 || $right < 1 || $left >= $right) {
                $this->addIssue(
                    $issues,
                    'INTERVALLE_INVALIDE',
                    'L’intervalle doit respecter 1 ≤ lft < rght.',
                    ['node' => $node],
                );
                continue;
            }
            $validNodes[$key] = $node;
            foreach (['lft' => $left, 'rght' => $right] as $field => $value) {
                $boundaries[$value][] = ['id' => $node['id'], 'field' => $field];
            }
        }

        foreach ($boundaries as $value => $references) {
            if (count($references) > 1) {
                $this->addIssue($issues, 'BORNE_DUPLIQUEE', 'Une borne est utilisée par plusieurs nœuds.', [
                    'value' => (int)$value,
                    'references' => $references,
                ]);
            }
        }
        $expected = $nodes === [] ? [] : range(1, count($nodes) * 2);
        $actual = array_map('intval', array_keys($boundaries));
        sort($actual);
        if ($actual !== $expected) {
            $this->addIssue($issues, 'BORNES_NON_CONTIGUES', 'Les bornes ne couvrent pas exactement 1..2n.', [
                'expected_maximum' => count($nodes) * 2,
                'actual_boundaries' => $actual,
            ]);
        }

        foreach ($validNodes as $node) {
            $parent = $node['parent_id'];
            if ($parent === null || !isset($validNodes[(string)$parent])) {
                continue;
            }
            $parentNode = $validNodes[(string)$parent];
            if (!($parentNode['lft'] < $node['lft'] && $node['rght'] < $parentNode['rght'])) {
                $this->addIssue($issues, 'ENFANT_HORS_PARENT', 'L’intervalle enfant sort de son parent.', [
                    'node' => $node,
                    'parent' => $parentNode,
                ]);
            }
        }

        uasort($validNodes, static fn(array $a, array $b): int => $a['lft'] <=> $b['lft']);
        $stack = [];
        foreach ($validNodes as $node) {
            while ($stack !== [] && end($stack)['rght'] < $node['lft']) {
                array_pop($stack);
            }
            if ($stack !== [] && $node['rght'] > end($stack)['rght']) {
                $this->addIssue(
                    $issues,
                    'INTERVALLES_CROISES',
                    'Deux intervalles se chevauchent sans être imbriqués.',
                    [
                        'first' => end($stack),
                        'second' => $node,
                    ],
                );
            }
            $stack[] = $node;
        }
    }

    /** @param array<string, array<string, mixed>> $nodes @param list<array<string, mixed>> $issues */
    private function checkLevels(array $nodes, array &$issues): void
    {
        foreach ($nodes as $key => $node) {
            if (!is_int($node['level'])) {
                continue;
            }
            $depth = 0;
            $current = $key;
            $visited = [];
            while ($nodes[$current]['parent_id'] !== null) {
                if (isset($visited[$current]) || !isset($nodes[(string)$nodes[$current]['parent_id']])) {
                    continue 2;
                }
                $visited[$current] = true;
                $current = (string)$nodes[$current]['parent_id'];
                $depth++;
            }
            if ($node['level'] !== $depth) {
                $this->addIssue($issues, 'NIVEAU_INCOHERENT', 'Le niveau stocké ne correspond pas à parent_id.', [
                    'node' => $node,
                    'expected_level' => $depth,
                ]);
            }
        }
    }

    /** @param array<string, array<string, mixed>> $nodes @param list<array<string, mixed>> $issues */
    private function checkDescendantCounts(array $nodes, array &$issues): void
    {
        $children = [];
        foreach ($nodes as $key => $node) {
            if ($node['parent_id'] !== null && isset($nodes[(string)$node['parent_id']])) {
                $children[(string)$node['parent_id']][] = (string)$key;
            }
        }
        foreach ($nodes as $key => $node) {
            if (!is_int($node['lft']) || !is_int($node['rght']) || $node['lft'] >= $node['rght']) {
                continue;
            }
            $descendants = $this->countDescendants((string)$key, $children, []);
            $intervalDescendants = intdiv($node['rght'] - $node['lft'] - 1, 2);
            if ($descendants !== $intervalDescendants) {
                $this->addIssue(
                    $issues,
                    'NOMBRE_DESCENDANTS_INCOHERENT',
                    'Le nombre de descendants diffère de parent_id.',
                    [
                        'node' => $node,
                        'parent_id_descendants' => $descendants,
                        'interval_descendants' => $intervalDescendants,
                    ],
                );
            }
        }
    }

    /** @param array<string, list<string>> $children @param array<string, bool> $visited */
    private function countDescendants(string $key, array $children, array $visited): int
    {
        if (isset($visited[$key])) {
            return 0;
        }
        $visited[$key] = true;
        $count = 0;
        foreach ($children[$key] ?? [] as $child) {
            $count += 1 + $this->countDescendants($child, $children, $visited);
        }

        return $count;
    }

    /** @param list<array<string, mixed>> $issues @param array<string, mixed> $details */
    private function addIssue(array &$issues, string $code, string $message, array $details): void
    {
        $issues[] = ['code' => $code, 'message' => $message, 'details' => $details];
    }

    /** @param list<array<string, mixed>> $issues @return array<string, mixed> */
    private function report(string $name, Table $table, int $nodeCount, array $issues): array
    {
        return [
            'table' => $name,
            'orm_alias' => $table->getAlias(),
            'node_count' => $nodeCount,
            'success' => $issues === [],
            'issues' => $issues,
            'check_command' => sprintf('bin/cake tree integrity check --table=%s', $name),
        ];
    }
}
