# ADR 0036 : Encapsulation de l'expédition SMTP (safeSend)

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte
La gestion des exceptions SMTP (déconnexion du serveur, identifiants invalides, timeout) entraînait une duplication des blocs `try/catch` dans les contrôleurs. Bien que l'outil `EmailLoggerTrait` (ADR 0035) ait simplifié la journalisation, la responsabilité de gérer l'échec de l'envoi incombait toujours au contrôleur, ce qui viole le principe de Responsabilité Unique (SRP - Single Responsibility Principle).

## Décision
1. **Création de la méthode `safeSend()`** : Ajoutée directement dans la classe mère `AppMailer`. Cette méthode enveloppe la méthode native `send()` du framework CakePHP.
2. **Capture et Log** : `safeSend()` exécute le bloc `try/catch`, trace le succès ou l'échec via le `EmailLoggerTrait` (en invoquant la façade statique `Log::write()`), et étouffe l'exception de niveau application en retournant un simple booléen.
3. **Contrôleurs Allégés** : Les contrôleurs délèguent totalement la sécurisation du processus au Mailer. L'appel se réduit à une seule ligne : `$mailer->safeSend('action', [$data]);`.

## Justification
Cette approche exploite la puissance de l'Héritage Objet. En centralisant la gestion du flux de sortie (le "Catch") dans l'`AppMailer`, les autres mailers métiers (comme `UserMailer`) restent de simples formateurs de données (Templates/Variables). Les contrôleurs ne sont plus exposés aux objets `Throwable` issus du client SMTP, garantissant une bien meilleure résilience de l'interface utilisateur.
