Rôle : Règles d'écriture de code à joindre dans tes prompts complexes pour forcer Gemini à produire du code propre et compatible avec ta codebase. 

# Standards de Code — DAETF (CakePHP 5 / PHP 8)

## 1. PHP & CakePHP 5
- Utiliser obligatoirement `declare(strict_types=1);` en haut de chaque fichier PHP.
- Typage strict systématique des arguments et des retours de fonctions.
- Utiliser `Cake\ORM\Query\SelectQuery` (et non `Cake\ORM\Query`).
- Préférer `TableRegistry::getTableLocator()->get('Model')` ou `$this->fetchTable('Model')`.
- Utiliser l'injection de dépendances ou les Domain Services dans `src/Service/` pour la logique complexe.
- Encapsuler toute opération d'écriture multi-tables dans une transaction SQL via `ConnectionManager::get('default')->transactional(...)`.

## 2. Vues et API JSON
- Les contrôleurs API doivent renvoyer les données paginées au format Tabulator via `TabulatorAdapter::adaptResponse()`.
- Toujours faire passer les données soumises par l'utilisateur dans `FieldAuthorizationService::filterRequestData()` avant l'association d'entité (`patchEntity`).

## 3. Sécurité et Exceptions
- Ne jamais désactiver les vérifications de sécurité sauf pour DebugKit.
- Lever des exceptions explicites (`RuntimeException`, `NotFoundException`, `ForbiddenException`) au lieu de retourner `false`.
