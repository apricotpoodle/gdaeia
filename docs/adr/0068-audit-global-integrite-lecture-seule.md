# ADR 0068 — Audit global d’intégrité en lecture seule

- **Date :** 2026-10-16
- **Statut :** Proposé
- **Dépendances :** ADR 0005, ADR 0006, ADR 0010, ADR 0067

## Contexte

Les contraintes référentielles SQL ne couvrent pas toutes les références
logiques de l’application : historiques conservés, cibles désactivées,
relations polymorphes, arbres `TreeBehavior`, étapes de workflow et vues SQL.
Les commandes de réparation doivent rester spécialisées et explicites.

## Décision

Une commande transversale `bin/cake integrity audit` est ajoutée. Elle est
strictement non mutative et orchestre des contrôles indépendants :

- `references` : catalogue explicite des références relationnelles ;
- `workflow` : invariants du cycle de validation ;
- `trees` : adaptation du checker existant des arbres ;
- `comments` : cibles des commentaires polymorphes cataloguées ;
- `views` : présence des vues SQL attendues (`urds`, `applicationformstatuses`,
  `currentvalidationroles` et `validation_visas`).

Le périmètre est sélectionnable avec `--scope`, et la sortie est disponible en
texte ou JSON avec `--format`. Les codes sont `0` si tout est cohérent, `1` si
une anomalie de données est détectée, et `2` en cas d’erreur technique ou de
configuration.

Le catalogue est maintenu manuellement. Il n’y a pas de déduction automatique
de toutes les colonnes suffixées `_id`, car elle produirait des faux positifs
sur les identifiants historiques, les snapshots et les relations polymorphes.
La commande globale ne répare jamais ; `validation repair-orphaned-sequences`
et les futurs outils spécialisés restent les seuls points d’entrée de mutation.

## Conséquences et limites

L’audit est industrialisable par cron, CI ou supervision et son résultat JSON
est stable pour les intégrations. Les contrôles sont bornés à un échantillon
d’anomalies et doivent donc être complétés par les commandes spécialisées lors
d’une remédiation. Une référence historique vers une cible désactivée est
acceptée lorsque le contrat du domaine le prévoit ; une référence physiquement
absente reste une anomalie. La présence d’une vue ne garantit pas à elle seule
la validité fonctionnelle de son SQL, qui relève des tests d’intégration et des
migrations versionnées.
