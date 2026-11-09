# Nyblit – ordres du jour et réunions

Petite application web (HTML/JS + PHP) qui se connecte à l'[API Nyblit](https://developers.nyblit.app/developers)
pour lister ses tâches, préparer un **ordre du jour**, mener la **réunion** (commentaires par tâche)
et envoyer le **compte rendu** par mail.

## Fonctions
- Tâches : liste filtrable (statut, projet, archivées), ajout rapide, terminer / reporter / archiver / modifier / supprimer.
- Projets : création, archivage, suppression. Workflows : lecture seule.
- Ordre du jour : sélection de tâches, durées, notes, ordre, puis envoi par
  invitation `.ics`, lien Outlook web, mail ou copie du texte.
- Réunions : mode « réunion en cours » avec commentaires par point, compte rendu généré en direct,
  envoi par mail, report des commentaires dans les notes des tâches Nyblit.
- Statistiques : nombre de fois où chaque tâche a été mise dans un ordre du jour.

## Architecture
- `index.html` : interface (un seul fichier).
- `proxy.php` : relaie les appels vers l'API. Le jeton reste côté serveur (pas de CORS, jamais exposé au navigateur).
- `stats.php`, `meetings.php` : stockage JSON dans `data/` (statistiques, réunions).
- `config.php` (non versionné) : jeton et URL de base.

## Installation
Prérequis : serveur web avec PHP et l'extension cURL (Apache ou nginx + PHP).

1. Copier les fichiers dans un dossier du serveur (ex. `/var/www/html/nyblit`).
2. Copier `config.example.php` en `config.php` et renseigner le jeton.
3. Donner les droits d'écriture au dossier à l'utilisateur du serveur web (création de `data/`).
4. **Protéger l'accès** (authentification HTTP, réseau local, VPN) : `proxy.php` donne accès au compte Nyblit.

Exemple Apache (authentification de base) :
```apache
<Directory /var/www/html/nyblit>
  AuthType Basic
  AuthName "Nyblit"
  AuthUserFile /etc/apache2/.htpasswd
  Require valid-user
</Directory>
```

## Personnalisation
- `SIGNATURE` en haut du script de `index.html` : signature ajoutée aux mails.
- Les participants se saisissent sous forme d'adresses e-mail séparées par des virgules.

## Limites
- Les compteurs comptent le clic sur un bouton d'envoi, pas l'envoi réel du mail.
- Application personnelle, sans gestion multi-utilisateurs.

## Licence
MIT, voir `LICENSE`.
