# ADR 0033 : Architecture de la couche Mailer (Principe DRY et SoC)

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte
Le flux de récupération de mot de passe nécessite l'envoi de courriels transactionnels contenant des liens sécurisés (jetons éphémères). Insérer la logique de formatage (Sujet, Modèle, Variables) et de configuration SMTP directement au sein du `UsersController` provoquerait un couplage fort et une violation de la Séparation des Préoccupations (SoC).

## Décision
1. **Création d'un socle d'infrastructure (`AppMailer`)** : Une classe de base abstraite centralise la configuration absolue des courriels sortants (Expéditeur générique système, configuration duale HTML/Texte).
2. **Spécialisation par domaine (`UserMailer`)** : Les envois d'emails liés aux utilisateurs héritent de l'`AppMailer`. Ils portent la responsabilité exclusive d'assembler la vue, les variables (`setViewVars`) et le sujet, allégeant ainsi le contrôleur.
3. **Sécurité Anti-Énumération** : Dans le contrôleur, que le compte existe ou non, et que l'envoi SMTP réussisse ou échoue (capturé par un bloc `try/catch` silencieux qui trace uniquement dans les logs système), le message renvoyé à l'interface est toujours un message de succès générique.

## Justification
Cette séparation garantit un code hautement testable et maintenable. Toute modification de l'identité d'expédition de l'entreprise s'opère dans l'unique fichier `AppMailer`, se propageant instantanément à l'ensemble du système (KISS & DRY).
