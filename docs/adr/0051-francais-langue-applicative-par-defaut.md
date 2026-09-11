# ADR 0051 : Français comme langue applicative par défaut

**Date :** 11 Septembre 2026  
**Statut :** Proposé  
**Dépendances :**
* [ADR 0019 : Internationalisation et ergonomie transversale des grilles de données](./0019-i18n-et-ergonomie-des-grilles.md)

---

## 1. Contexte

L'application DAETF s'adresse à un public francophone. Sa configuration CakePHP utilise toutefois `en_US` comme locale de repli lorsque la variable d'environnement `APP_DEFAULT_LOCALE` n'est pas définie. Les messages de validation fournis par le framework, ainsi que le formatage des dates, nombres et devises, peuvent donc apparaître en anglais selon l'environnement de déploiement.

Cette situation est incohérente avec l'interface métier en français et avec la traduction déjà appliquée aux grilles Tabulator par l'ADR 0019. Les messages d'erreur doivent être compréhensibles par défaut, sans imposer une configuration locale particulière à chaque environnement.

## 2. Décision

1. **Locale de repli** : la locale applicative par défaut est `fr_FR`. La configuration CakePHP utilise cette valeur lorsque `APP_DEFAULT_LOCALE` est absente.
2. **Configuration explicite** : `APP_DEFAULT_LOCALE` reste disponible pour un déploiement qui nécessiterait une autre locale. Toute dérogation doit être définie explicitement dans sa configuration d'environnement.
3. **Messages utilisateur** : les messages de validation et d'erreur exposés aux utilisateurs sont rédigés en français. Les règles métier et contraintes d'intégrité dont le message n'est pas fourni par CakePHP doivent définir un message français explicite.
4. **Portée** : cette règle s'applique aux interfaces HTML, aux réponses JSON de l'API, aux courriels et aux nouveaux modules applicatifs.

## 3. Justifications

Une locale de repli unique garantit un comportement identique en développement, test et production lorsque l'environnement n'a pas été personnalisé. Elle évite de disperser des traductions ponctuelles dans les contrôleurs et permet au mécanisme i18n de CakePHP de traiter uniformément les formats et les messages standards.

La conservation de la variable d'environnement respecte les principes de configuration externalisée et maintient la possibilité d'une internationalisation future, sans faire de l'anglais le comportement implicite de l'application métier.

## 4. Conséquences

### Positives

* Les utilisateurs reçoivent des libellés, formats et messages cohérents en français par défaut.
* Les validations standard de CakePHP bénéficient de la locale applicative sans traitement spécifique dans chaque contrôleur.
* Les environnements de déploiement restent configurables explicitement par `APP_DEFAULT_LOCALE`.

### Négatives

* Les messages métier codés en anglais doivent être progressivement recensés et traduits.
* Une application cliente consommant l'API doit accepter que les messages utilisateur soient en français par défaut.
* Toute future prise en charge multilingue devra définir une stratégie de sélection de locale par utilisateur ou par requête.
