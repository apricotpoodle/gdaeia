# ADR 0077 — Suivi idempotent des expéditions de notifications

**Date :** 25 novembre 2026
**Statut :** Proposé
**Dépendances :** ADR 0005, ADR 0006, ADR 0034, ADR 0036, ADR 0058 et ADR 0071.

## Contexte

Les commandes de notifications GDAE marquent actuellement une période globale
comme traitée même lorsque certains destinataires échouent. Une nouvelle
exécution ne permet donc pas de réessayer uniquement les échecs; elle peut
également renvoyer des courriels déjà acceptés par le transport. Les envois
temps réel ne disposent pas d’une clé persistante commune avec les commandes.
Les paramètres `workflow_settings` ne doivent pas contenir cet état
opérationnel, conformément à l’ADR 0058.

## Décision proposée

1. Créer la table `notification_deliveries` pour mémoriser une livraison par
   événement logique et adresse destinataire normalisée.
2. Stocker uniquement une clé SHA-256, une empreinte SHA-256 du destinataire,
   le type de notification, l’état, le nombre de tentatives et les horodatages.
   Ne pas conserver l’adresse en clair, le corps du courriel ni les données de
   la DAE.
3. Rendre la clé d’idempotence unique. Réclamer une ligne par insertion ou
   mise à jour conditionnelle InnoDB, considérer une claim active pendant
   quinze minutes, ignorer un succès confirmé et permettre la reprise d’un
   échec ou d’une claim expirée.
4. Journaliser le destinataire sous forme d’empreinte pour les notifications
   GDAE; continuer à utiliser `AppMailer::safeSend()` et le canal de courriels
   des ADR 0034 et 0036.
5. Ne pas modifier les vues SQL. La table suit la procédure Phinx de l’ADR 0006
   et le modèle MySQL/InnoDB de l’ADR 0005.

## Justification

Une clé unique par destinataire et période ou événement évite de renvoyer les
succès lors d’une relance et rend les échecs identifiables. L’état de
notification reste séparé du paramétrage métier. L’empreinte limite les
données personnelles persistées tout en permettant la déduplication.

SMTP ne permet pas de garantir l’exactement-une-fois si le serveur a accepté
le courriel puis que le processus s’arrête avant l’enregistrement du succès.
Le registre garantit l’idempotence des succès confirmés et la reprise des
échecs connus, avec une durée de claim bornée.

## Conséquences

### Positives

- Les exécutions répétées ignorent les envois confirmés et retentent les
  échecs connus.
- Les commandes peuvent continuer après un destinataire défaillant et
  retourner un code d’erreur agrégé pour Airflow.
- Aucun contenu de DAE ni adresse courriel en clair n’est conservé dans le
  registre.

### Négatives

- La table grandit avec le volume de notifications et nécessite une règle de
  rétention ultérieure si le volume opérationnel le justifie.
- Un résultat SMTP ambigu peut encore produire un doublon lors d’une reprise.
- Les commandes récurrentes et les notifications temps réel doivent partager
  les clés et l’état de livraison pour rester cohérentes.
