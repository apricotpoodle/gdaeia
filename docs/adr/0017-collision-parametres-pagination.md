# ADR 0017 : Collision des paramètres de requête entre Tabulator et CakePHP

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Par défaut, Tabulator envoie ses requêtes de tri via le paramètre d'URL `sort[]` (sous forme de tableau). Or, le composant `Paginator` natif de CakePHP intercepte automatiquement la clé `sort` en attendant une chaîne de caractères (String), causant une erreur fatale PHP `preg_match()`.

## Décision
1. **Front-end :** Utilisation de l'option `dataSendParams` dans le `TabulatorBuilder` pour renommer le paramètre d'envoi en `sorters` (et `filters` pour la recherche).
2. **Back-end :** Déclaration de `'sortableFields' => []` dans les options de la méthode `paginate()` des contrôleurs API pour désactiver totalement le tri natif automatique de CakePHP. Le tri est désormais de la responsabilité exclusive du `TabulatorAdapter`.

## Justification
Cette solution est non-intrusive. Elle permet de conserver l'usage de la méthode native `$this->paginate()` de CakePHP pour le calcul des pages, tout en évitant les collisions de mots-clés réservés sur les query strings.