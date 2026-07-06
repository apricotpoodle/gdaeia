# ADR 0019 : Internationalisation (i18n) et Ergonomie Transversale des Grilles de Données

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'application s'adresse à un public francophone. Les grilles de données fournies nativement par Tabulator affichent des contrôles de navigation en langue anglaise. De plus, lors des phases de requêtage lourd sur l'API, l'absence de retour visuel altère l'expérience utilisateur (impression de blocage).

## Décision
Intégration systématique des directives d'UX dans le constructeur de base `TabulatorBuilder` :
1. **i18n** : Injection d'un dictionnaire de traduction `'fr-fr'` couvrant l'ensemble du système de pagination et des filtres.
2. **Indicateur d'activité** : Activation de l'option `ajaxLoader: true` couplée à un gabarit HTML personnalisé pour notifier visuellement les phases de synchronisation réseau.
3. **Traitement de la vacuité** : Déclaration de l'option `placeholder` affichant un message explicite en français en cas de retour d'un jeu de données nul.
4. **Isolation Graphique** : Les règles CSS de surcharge (loading, placeholder vide) sont strictement isolées dans `webroot/css/tabulator/custom-theme.css` pour préserver la modularité des composants et éviter la pollution de la feuille de style globale. Un guide de proximité `webroot/css/tabulator/README.md` encadre les futures contributions graphiques.

## Justification
L'intégration de ces fonctionnalités dans le constructeur abstrait respecte à 100% les principes DRY et KISS. L'ensemble de l'application bénéficie d'une charte graphique et fonctionnelle unifiée sans surcharge cognitive pour le développeur lors de la création d'un nouveau module.

