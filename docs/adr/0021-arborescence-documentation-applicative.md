# ADR 0021 : Emplacement du répertoire de documentation dans un contexte de Boilerplate

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
La racine du projet (contenant les configurations Docker et d'infrastructure globale) est isolée et gérée comme un dépôt "boilerplate" distinct. Seul le sous-répertoire `/app` constitue le dépôt Git actif de l'application métier. Les documents d'architecture (ADR) doivent impérativement être versionnés avec l'application qu'ils décrivent.

## Décision
Le répertoire `/docs` est positionné de manière définitive à la racine du dépôt applicatif, soit dans `app/docs/`.

## Justification
* **Gouvernance et Versioning :** Les décisions d'architecture évoluant au même rythme que les fonctionnalités métier, elles doivent résider dans le même historique Git que le code source (`/app`).
* **Séparation des cycles de vie :** Évite de polluer le dépôt du boilerplate d'infrastructure avec des documentations purement fonctionnelles ou applicatives.
* **Sécurité :** Situé en dehors de `app/webroot/`, le dossier `app/docs/` bénéficie de la protection native du framework et ne peut en aucun cas être exposé publiquement sur le réseau Web.
