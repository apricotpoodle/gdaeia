# ADR 0049 : Intégration de TreeselectJS pour les structures hiérarchiques (Départements)

**Date :** 31 Août 2026
**Statut :** Accepté
**Dépendances :**
*   [ADR 0009 : Implémentation du module Utilisateurs via API et Tabulator](./0009-implementation-tabulator-users.md) [2]
*   [ADR 0012 : Modélisation manuelle des relations bidirectionnelles multiples](./0012-relations-multiples-orm.md) [3]
*   [ADR 0030 : Modernisation de l'infrastructure front-end via les modules ES6](./0030-modernisation-scripts-modules-es6.md) [4]
*   [ADR 0037 : Hébergement Local des Dépendances Front-end](./0037-hebergement-local-dependances-frontend.md) [5]
*   [ADR 0047 : Centralisation de l'identité dans AppView et gestion des scripts de vue](./0047-normalisation-vues-identity-assets-js.md) [6]

---

## 1. Contexte
L'application gère une structure organisationnelle complexe où la table `departments` présente des liaisons d'héritage hiérarchique (arborescence parents-enfants) ainsi que des relations bidirectionnelles multiples croisées avec d'autres référentiels (tels que les codes CGR), ce qui nécessite des corrections manuelles au niveau de l'ORM [7, 8].

Pour l'attribution d'une demande de recrutement (`applicationforms`) ou l'affectation d'un utilisateur à un périmètre de départements [9, 10], l'interface utilisateur (UI) requiert un sélecteur de données ergonomique capable d'afficher cette hiérarchie. Les balises HTML standards `<select>` s'avèrent inadaptées pour représenter des relations parents-enfants profondes et provoquent des surcharges visuelles.

Nous avons retenu la bibliothèque **TreeselectJS** [11]. L'enjeu est de définir un cadre d'intégration robuste qui respecte :
1.  Notre politique de **Séparation des Préoccupations (SoC)** (le contrôleur Web livre le squelette HTML, l'API fournit les nœuds hiérarchisés) [12].
2.  L'isolation stricte des fichiers JavaScript de vue, interdisant tout script inline [13].
3.  L'hébergement local des dépendances pour garantir la souveraineté des données, l'indépendance vis-à-vis des CDN et la conformité RGPD [14, 15].
4.  Nos standards de performance (KISS) et d'analyse statique [16].

---

## 2. Décisions

### A. Hébergement local et Importation ES6
*   Conformément à l'ADR 0037, les fichiers sources de la bibliothèque `TreeselectJS` seront téléchargés et hébergés localement sous `webroot/assets/treeselectjs/` [14].
*   Conformément à l'ADR 0030, la bibliothèque sera importée exclusivement sous forme de module ES6 (`type="module"`) au sein de nos scripts d'orchestration [17].

### B. Format d'échange API (Contrat de données)
*   L'API CakePHP exposera les données hiérarchiques des départements via une route JSON dédiée `/api/departments/tree.json`, forçant l'extension `.json` [12].
*   Le payload renvoyé par l'API adoptera la structure récursive attendue par `TreeselectJS` [18, 19] :
    *   `name` (String, obligatoire) : Libellé du département [19].
    *   `value` (String ou Number, obligatoire et strictement unique dans l'arbre) [19, 20].
    *   `children` (Array d'objets du même type, obligatoire) [18, 19].
    *   `disabled` (Boolean, optionnel) [19].
*   L'arborescence sera construite en exploitant le comportement natif `TreeBehavior` de l'ORM de CakePHP via la méthode de recherche `find('threaded')`.

### C. Encapsulation par un Wrapper Front-End
Pour maximiser la réutilisation (DRY) et simplifier l'utilisation du composant par l'équipe, nous créons un module d'infrastructure réutilisable nommé `TreeselectWrapper` sous `webroot/js/core/Components/TreeselectWrapper.js` :
1.  **Directives Système par Défaut** :
    *   `isBoostedRendering: true` : Toujours activé pour optimiser le rendu et prévenir les ralentissements sur les arbres volumineux via l'IntersectionObserver [18, 20].
    *   `clearable: true` et `searchable: true` : Activés par défaut pour fluidifier l'expérience de recherche [21].
    *   `appendToBody: false` : Désactivé par défaut pour éviter que la liste ne soit injectée hors du conteneur parent, sauf cas d'intégration complexe (par exemple dans une grille Tabulator) [22, 23].
2.  **Liaison bidirectionnelle au DOM (Formulaires CakePHP)** :
    *   Le template de vue `.php` définit un conteneur d'ancrage HTML vide (ex: `<div id="dept-tree"></div>`) et un champ masqué `<input type="hidden">` portant le nom du champ attendu par l'ORM CakePHP lors du POST (ex: `name="department_id"`) [18].
    *   Le `TreeselectWrapper` écoute l'événement d'émission `input` de `TreeselectJS` [24] et met automatiquement à jour la valeur de l'élément `<input>` masqué associé [18].

### D. Personnalisation Visuelle (Theming)
*   Aucune injection de style inline ou de règles CSS directes n'est tolérée sur le composant.
*   L'alignement graphique avec notre charte (Bootstrap 5.3) s'effectue en surchargeant les propriétés personnalisées CSS de la bibliothèque (`--treeselectjs-*`) directement sur la pseudo-classe `:root` ou l'élément `body` dans notre feuille de style `custom-theme.css` [23].

### E. Sécurité et Habilitations (Défense en Profondeur)
*   **Contrôle visuel (Front-end)** : Si un utilisateur n'a pas les droits d'écriture sur le champ de destination (vérifié via le schéma d'ACL de champs), le `TreeselectWrapper` sera instancié avec l'option `disabled: true` [18, 25].
*   **Validation stricte (Back-end)** : Le `FieldAuthorizationService` filtrera les requêtes POST entrantes pour valider que le département sélectionné fait partie du périmètre autorisé pour l'utilisateur connecté avant tout `patchEntity` [25, 26].

---

## 3. Justifications
*   **Haute Cohésion & Faible Couplage (SOLID)** : Le contrôleur Web se décharge de toute logique d'arbre [12]. L'API distribue des données JSON standardisées, et le Wrapper JS centralise la complexité d'affichage du widget tierce [12, 18].
*   **Souveraineté et Performance** : L'utilisation d'assets hébergés localement élimine la dépendance vis-à-vis de serveurs CDN tiers (fin des risques de pannes de type Supply Chain ou de fuites d'IP des utilisateurs) [15].
*   **Simplicité de Maintenance (KISS)** : L'utilisation du Wrapper évite d'éparpiller des instanciations complexes de `new Treeselect()` dans chaque orchestrateur de vue.

---

## 4. Conséquences

### Positives :
*   **Expérience utilisateur (UX) supérieure** : Recherche à la volée, repliement fluide des branches et indicateur du nombre d'enfants [21, 22].
*   **Conformité Sécurité** : Pas de scripts inline [13], conformité RGPD [15], et respect du principe de défense en profondeur [27].
*   **Analyse Statique Stable** : Le code du wrapper et des orchestrateurs de vue respecte les directives ES6+ et bénéficie de commentaires JSDoc rigoureux pour l'IDE et l'analyse statique [16, 17].

### Négatives :
*   **Double Liaison** : Obligation d'associer un élément d'ancrage DOM à un champ masqué (`<input type="hidden">`) pour propager la valeur sélectionnée lors de la soumission standard de formulaires non-AJAX [18].

---

## 5. Exemple d'implémentation type

### Backend (API CakePHP)
```php
<?php
// src/Controller/Api/DepartmentsController.php
namespace App\Controller\Api;

use App\Controller\AppController;

class DepartmentsController extends AppController
{
    public function tree()
    {
        $this->request->allowMethod(['get']);

        // Utilisation du TreeBehavior de CakePHP pour construire l'arborescence
        $departments = this->Departments->find('threaded')
            ->select(['id', 'parent_id', 'name'])
            ->toArray();

        // Transformation récursive au format TreeselectJS
        $formattedTree = (this->formatForTreeselect($departments);

        $this->set([
            'success' => true,
            'data' => $formattedTree,
            '_serialize' => ['success', 'data']
        ]);
    }

    private function formatForTreeselect(array $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $result[] = [
                'value' => $node->id,
                'name' => $node->name,
                'children' => !empty($node->children) ? this->formatForTreeselect($node->children) : []
            ];
        }
        return $result;
    }
}
```

### Frontend (Orchestrateur de Vue)
```js
// webroot/js/views/Users/edit.js
import Treeselect from '../../assets/treeselectjs/treeselectjs.mjs'; // Module ES6 local [14, 17]

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('dept-tree-container');
    const inputHidden = document.getElementById('department-id');

    if (container && inputHidden) {
        fetch('/api/departments/tree.json', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(payload => {
            // Instanciation de TreeselectJS
            const treeselect = new Treeselect({
                parentHtmlContainer: container,
                value: inputHidden.value ? [parseInt(inputHidden.value)] : [],
                options: payload.data,
                isSingleSelect: true, // Mode dropdown simple [18, 20]
                showTags: false,       // Rendu comme un menu déroulant classique [18, 20]
                isBoostedRendering: true, // Optimisation pour les grands arbres [18, 20]
                placeholder: "Sélectionnez un département..."
            });

            // Synchronisation de la sélection vers l'input masqué pour CakePHP
            treeselect.srcElement.addEventListener('input', (e) => {
                inputHidden.value = e.detail; // Récupère la valeur sélectionnée [24]
            });
        });
    }
});
```
