# Backend Auto-Key — Gestion d'images

## Démarrage rapide

### 1. Définir le mot de passe admin
Sur le serveur (ou en local avec PHP), générez un hash :
```bash
php admin/generate-password.php "votre_mot_de_passe"
```
Copiez le hash obtenu et collez-le dans `admin/admin.php` à la ligne :
```php
define('ADMIN_PASS_HASH', '$2y$10$...');
```

### 2. Protéger le dossier admin (recommandé)
Ajoutez dans `admin/.htaccess` :
```
AuthType Basic
AuthName "Admin Auto-Key"
AuthUserFile /chemin/absolu/admin/.htpasswd
Require valid-user
```
Et générez `.htpasswd` :
```bash
htpasswd -c admin/.htpasswd admin
```

### 3. Accéder au panneau
Rendez-vous sur `https://www.auto-key.fr/admin/admin.php`
- Identifiant : `admin`
- Mot de passe : celui que vous avez défini

## Fonctionnalités

### 🖼️ Images
- **Upload drag & drop** (JPG, PNG, WebP, SVG, GIF, max 5MB)
- **Bibliothèque** avec aperçu, taille, date, usage
- **Suppression** (sauf si utilisée)
- **Copier l'URL** d'une image

### ⚡ Assignations
- Assigner chaque image à un emplacement du site :
  - Hero accueil
  - Open Graph (réseaux sociaux)
  - Logo
  - 6 images de services (duplication, perdue, réparation, diagnostic, multimédia, urgence)
- **Application automatique** : les images sont copiées vers les noms de fichiers attendus par le site (ex: `service-duplication.jpg`)
- Le site n'a pas besoin d'être modifié, les noms de fichiers restent identiques

### ⚙️ Paramètres
- Nom du site
- Téléphone
- Email
- Adresse
- Horaires
- Sauvegarde dans `admin/config.json`

## Sécurité

- Authentification par session PHP
- Hash bcrypt du mot de passe
- Vérification du type MIME des fichiers uploadés
- Vérification de l'extension des fichiers
- Taille maximale 5MB
- Protection contre la suppression d'images utilisées

## API (optionnel)

- `GET admin.php?action=list` — liste des images
- `GET admin.php?action=api` — JSON public des images
- `POST admin.php?action=upload` — upload (FormData avec `file`)
- `POST admin.php?action=assign` — assigner `{role, image}`
- `POST admin.php?action=delete` — supprimer `{name}`
- `GET/POST admin.php?action=config` — paramètres du site