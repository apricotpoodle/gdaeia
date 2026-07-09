# ADR 0037 : Hébergement Local des Dépendances Front-end (Abandon des CDN)

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte
L'application utilise des librairies JavaScript et CSS tierces (ex: Tabulator, Bootstrap, FontAwesome). Historiquement, le chargement via CDN (Content Delivery Network comme `unpkg` ou `cdnjs`) était privilégié pour économiser la bande passante et bénéficier du cache partagé des navigateurs.

## Décision
1. **Abandon des CDN** : À terme, l'utilisation de liens CDN externes sera proscrite dans le code source (Layouts, Helpers, Vues).
2. **Hébergement Local (Assets)** : Toutes les librairies tierces nécessaires au front-end doivent être téléchargées physiquement et stockées dans le répertoire `webroot/assets/` (pour éviter tout conflit Git avec le dossier `vendor` de Composer).
3. **Lazy Loading** : Ces ressources locales seront injectées dynamiquement via les Helpers CakePHP ou importées via les modules ES6, uniquement sur les pages qui en ont l'usage.

## Justification
* **Conflits Git/Composer** : L'utilisation du répertoire `assets` contourne proprement les règles `.gitignore` natives de l'écosystème PHP.
* **RGPD et Confidentialité** : Le chargement de ressources depuis un CDN expose l'adresse IP de nos utilisateurs à des services tiers sans leur consentement explicite. Le chargement local garantit que les données de navigation restent confinées à notre infrastructure.
* **Sécurité (Supply Chain)** : Héberger localement immunise l'application contre le piratage ou la corruption d'un service CDN externe.
* **Environnement de Développement** : Permet aux développeurs de travailler sur l'application (via Docker) en mode totalement hors-ligne.
* **Fin du Cache Partagé** : Les navigateurs modernes ayant partitionné leur cache par domaine, le gain de performance historique des CDN a disparu.
