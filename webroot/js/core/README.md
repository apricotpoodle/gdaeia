# Module Core : Utilitaires Transverses

Ce dossier contient les scripts fondamentaux de l'application, agnostiques vis-à-vis des modules métiers, servant de socle pour l'interface utilisateur.

## FlashManager (`FlashManager.js`)

Classe modulaire responsable des toasts produits par CakePHP et par les scripts JavaScript. Le même rendu est disponible dans les deux layouts grâce à `webroot/css/flash.css`.

### Caractéristiques
* **Conteneur partagé** : Réutilise `#flash-container` et le crée si nécessaire. Chaque toast peut être fermé indépendamment.
* **Durée d'affichage** : Tous les types disparaissent après cinq secondes par défaut. Les Flash serveur gardent le temps restant si le même écran est rechargé avant leur expiration ; les messages AJAX ne sont pas restaurés.
* **Initialisation** : `app.js`, chargé comme module par les layouts, appelle `FlashManager.init()` une fois que le DOM est prêt.

### Utilisation (API Statique après Importation)
Le gestionnaire s'invoque de n'importe où (Factories, Observers, Vues) après avoir été importé :

```javascript
import { FlashManager } from '../FlashManager.js';

// Succès (disparaît après 5 secondes)
FlashManager.success("L'enregistrement a été mis à jour.");

// Erreur (disparaît aussi après 5 secondes)
FlashManager.error("Erreur serveur : Opération interdite.");

// Avertissement et Information
FlashManager.warning("Attention, cette action est irréversible.");
FlashManager.info("Le téléchargement va commencer.");

// Appel manuel avec durée personnalisée (0 = ne disparaît pas automatiquement)
FlashManager.show("Message personnalisé", "primary", 10000);
```

Le contenu passé aux méthodes JavaScript peut contenir du HTML de confiance. Échapper les données variables avant de les insérer dans ce HTML.

## 🛑 RÈGLE IMPÉRATIVE : Gouvernance des Modules (ADR 0030)
Depuis l'adoption de l'ADR 0030, l'intégralité du code de ce dossier et de ses sous-dossiers (Tabulator/) fonctionne exclusivement sous la norme des Modules ES6.

- Zéro pollution globale : Aucune classe, constante ou instance ne doit être assignée à l'objet global window. L'étanchéité doit être totale.

- Exportations explicites : Tout composant transverse ou outil utilitaire doit être déclaré avec le mot-clé export (ou export const pour les instances uniques/Singletons).

- Inclusions côté Templates PHP : Les layouts n'incluent pas directement les scripts de ce répertoire. Les vues métiers et le point d'entrée commun `app.js` importent explicitement leurs dépendances et sont chargés avec l'attribut `type="module"`.

### Exemples d'alignements sémantiques standards :
```JavaScript
// Importation d'une classe usine
import { TabulatorFactory } from './Tabulator/TabulatorFactory.js';

// Importation d'un bus d'événements pré-instancié (Singleton)
import { globalTabulatorObserver } from './Tabulator/TabulatorObserver.js';
```
