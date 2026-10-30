# ADR 0071 — Fuseau horaire applicatif Europe/Paris et stockage UTC

**Date :** 30 octobre 2026

**Statut :** Proposé

**Dépendances :**

- ADR 0002 — Connexion à la base de données.
- ADR 0051 — Français comme langue applicative par défaut.

## Contexte

L'application est utilisée par des opérateurs situés en France. Le fuseau
horaire applicatif de repli était UTC, ce qui pouvait rendre les heures
affichées et les dates générées difficiles à interpréter pour les utilisateurs.

Les connexions MySQL sont déjà configurées en UTC. Il faut donc distinguer le
fuseau utilisé par l'application et l'affichage du fuseau utilisé pour le
stockage et les échanges techniques.

## Décision proposée

1. Le fuseau horaire applicatif par défaut est Europe/Paris.
2. La configuration utilise APP_DEFAULT_TIMEZONE afin de permettre une
   surcharge explicite par environnement, avec Europe/Paris comme valeur de
   repli.
3. Le fuseau applicatif est appliqué par PHP via date_default_timezone_set()
   et par CakePHP pour les formats de dates et d'heures.
4. Les connexions et le stockage MySQL restent en UTC afin d'éviter les
   ambiguïtés liées aux changements d'heure saisonniers.
5. Le fuseau actuellement utilisé est affiché dans la barre de navigation afin
   de rendre le contexte temporel visible aux opérateurs.
6. Les tests vérifient à la fois la configuration CakePHP, le fuseau PHP et la
   présence du fuseau dans l'interface Web.

## Justifications

Europe/Paris correspond au contexte opérationnel des utilisateurs et prend en
charge automatiquement les changements d'heure d'été et d'hiver. La
conservation d'UTC pour la base de données sépare le stockage canonique de la
présentation locale et facilite les échanges entre environnements.

La variable d'environnement respecte la configuration externalisée sans
multiplier les valeurs codées en dur. L'affichage dans la navigation permet
également de diagnostiquer rapidement une configuration d'environnement
incorrecte.

## Conséquences

### Positives

- Les dates et heures présentées aux utilisateurs correspondent au contexte
  français.
- Le comportement est explicite et testable dans tous les environnements.
- Le stockage UTC reste stable et indépendant du changement d'heure saisonnier.

### Négatives

- Une surcharge d'environnement différente de Europe/Paris modifie le
  comportement affiché et doit être documentée.
- Les intégrations externes doivent continuer à préciser si elles échangent
  des dates en UTC ou des dates locales.
- Les tests et les exemples de configuration doivent conserver la convention
  Europe/Paris.
