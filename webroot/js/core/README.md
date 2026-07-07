# Module Core : Utilitaires Transverses

Ce dossier contient les scripts fondamentaux de l'application, agnostiques vis-à-vis des modules métiers, servant de socle pour l'interface utilisateur.

## FlashManager (`FlashManager.js`)

Classe statique responsable de la génération, de l'affichage et de la destruction automatique des messages de notification dynamiques (Alerts / Toasts) basés sur Bootstrap 5.

### Caractéristiques
* **Zéro Dépendance DOM** : Ne nécessite aucune balise HTML préexistante. Le conteneur parent (`#dynamic-flash-container`) est généré et positionné (en haut à droite, au-dessus du contenu) dynamiquement lors du premier appel.
* **Auto-destruction (Garbage Collection)** : Les messages disparaissent d'eux-mêmes après un délai configurable, gardant l'arbre DOM propre et performant.

### Utilisation (API Statique)
Le gestionnaire s'invoque de n'importe où (Factories, Observers, Vues) :

```javascript
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

##  🛑 NOUVELLE RÈGLE : Gouvernance des Modules (ADR 0030)

Depuis l'adoption de l'ADR 0030, l'intégralité du code de ce dossier fonctionne sous la norme des **Modules ES6**.
- Aucun fichier de ce dossier ne doit être inclus via une balise `<script>` classique dans le layout général PHP.
- Toutes les classes (`FlashManager`, `TabulatorBuilder`, etc.) doivent obligatoirement être exportées via le mot-clé `export`.
- Les scripts de premier niveau (orchestrateurs de pages comme `views/Users/index.js`) doivent charger ces composants uniquement via l'instruction `import`.

### Exemple d'alignement sémantique standard :
```javascript
import { FlashManager } from '../FlashManager.js';
import { TabulatorFactory } from './Tabulator/TabulatorFactory.js';
```


