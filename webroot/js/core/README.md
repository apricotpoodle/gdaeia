# Module Core : Utilitaires Transverses

Ce dossier contient les scripts fondamentaux de l'application, agnostiques vis-à-vis des modules métiers, servant de socle pour l'interface utilisateur.

## FlashManager (`FlashManager.js`)

Classe modulaire responsable de la génération, de l'affichage et de la destruction automatique des messages de notification dynamiques (Alerts / Toasts) basés sur Bootstrap 5.

### Caractéristiques
* **Zéro Dépendance DOM** : Ne nécessite aucune balise HTML préexistante. Le conteneur parent (`#dynamic-flash-container`) est généré et positionné (en haut à droite, au-dessus du contenu, z-index 1055) dynamiquement lors du premier appel.
* **Auto-destruction (Garbage Collection)** : Les messages disparaissent d'eux-mêmes après un délai configurable, en attendant la fin des animations CSS avant la suppression physique, gardant l'arbre DOM propre et performant.

### Utilisation (API Statique après Importation)
Le gestionnaire s'invoque de n'importe où (Factories, Observers, Vues) après avoir été importé :

```javascript
import { FlashManager } from '../FlashManager.js';

// Succès (disparaît après 5 secondes)
FlashManager.success("L'enregistrement a été mis à jour.");

// Erreur (disparaît après 7 secondes par défaut, laisse le temps de lire)
FlashManager.error("Erreur serveur : Opération interdite.");

// Avertissement et Information
FlashManager.warning("Attention, cette action est irréversible.");
FlashManager.info("Le téléchargement va commencer.");

// Appel manuel avec durée personnalisée (0 = ne disparaît pas automatiquement)
FlashManager.show("Message personnalisé", "primary", 10000);
```

## 🛑 RÈGLE IMPÉRATIVE : Gouvernance des Modules (ADR 0030)
Depuis l'adoption de l'ADR 0030, l'intégralité du code de ce dossier et de ses sous-dossiers (Tabulator/) fonctionne exclusivement sous la norme des Modules ES6.

- Zéro pollution globale : Aucune classe, constante ou instance ne doit être assignée à l'objet global window. L'étanchéité doit être totale.

- Exportations explicites : Tout composant transverse ou outil utilitaire doit être déclaré avec le mot-clé export (ou export const pour les instances uniques/Singletons).

- Inclusions côté Templates PHP : Il est strictement interdit d'inclure les scripts de ce répertoire via le Helper HTML traditionnel dans les layouts génériques CakePHP (default.php). L'arbre complet des dépendances est résolu automatiquement par le navigateur à partir du script d'index de la vue chargée avec l'attribut type="module".

### Exemples d'alignements sémantiques standards :
```JavaScript
// Importation d'une classe usine
import { TabulatorFactory } from './Tabulator/TabulatorFactory.js';

// Importation d'un bus d'événements pré-instancié (Singleton)
import { globalTabulatorObserver } from './Tabulator/TabulatorObserver.js';
```
