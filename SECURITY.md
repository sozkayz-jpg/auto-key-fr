# 🔒 Audit de Sécurité — allo-cle-auto.fr

**Date** : juillet 2026
**Niveau de sécurité** : Bon pour un site vitrine (B+)

---

## ✅ Mesures de sécurité actives

### Backend / API (`api/contact.php`)
- ✅ Échappement HTML de tous les inputs (`htmlspecialchars`)
- ✅ Validation email stricte (`FILTER_VALIDATE_EMAIL`)
- ✅ Validation téléphone français (regex `^(\+33|0)[1-9](\d{2}){4}$`)
- ✅ Longueurs max sur tous les champs (anti-abus)
- ✅ Anti-spam : limite à 3 liens max dans le message
- ✅ Anti-spam : honeypot (champ invisible)
- ✅ Anti-spam : rate-limiting par IP (5 requêtes / 15 min)
- ✅ Headers mail anti-injection (pas de `\r\n` dans le sujet)
- ✅ CORS restrictif (seulement `allo-cle-auto.fr`)
- ✅ Logs de toutes les soumissions dans `logs/contact.log`
- ✅ Validation année (1900-2099)

### Admin (`admin/admin.php`)
- ✅ Hachage mot de passe bcrypt (`$2y$10$`)
- ✅ `password_verify()` pour la comparaison (anti-timing-attack)
- ✅ Délai artificiel sur les échecs (anti-énumération)
- ✅ Rate limiting login (5 tentatives / 15 min par IP)
- ✅ Sessions sécurisées (httponly, secure, samesite=Strict)
- ✅ Régénération d'ID de session après login
- ✅ Vérification IP + User-Agent à chaque requête
- ✅ Tokens CSRF sur toutes les actions POST (32 bytes random)
- ✅ `basename()` + `realpath()` sur tous les chemins d'upload (anti path-traversal)
- ✅ Double vérification extensions + MIME réel (anti double-extension)
- ✅ Blocage des fichiers `.php`, `.phtml`, `.phar`, `.pl`, `.py`, `.sh`, `.cgi`
- ✅ `chmod 0644` après upload
- ✅ Log de toutes les actions admin
- ✅ Désactivation `expose_php`
- ✅ Suppression de SVG (peut contenir du JS) → remplacé par PNG/WebP

### Frontend (`.htaccess`)
- ✅ `X-Content-Type-Options: nosniff` (anti MIME sniffing)
- ✅ `X-Frame-Options: SAMEORIGIN` (anti clickjacking)
- ✅ `X-XSS-Protection: 1; mode=block`
- ✅ `Referrer-Policy: strict-origin-when-cross-origin`
- ✅ `Permissions-Policy` restrictive
- ✅ `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` (HSTS)
- ✅ **Content-Security-Policy** complète (CSP)
- ✅ HTTPS forcé (301)
- ✅ Redirection domaine unique (auto-key.fr → allo-cle-auto.fr)
- ✅ Blocage accès à `config.json`, `admin.log`, `.env`, `.git/`
- ✅ Blocage exécution PHP dans `assets/img/`
- ✅ `Options -Indexes` (anti listing dossiers)
- ✅ `ServerSignature Off` + `ServerTokens Prod`
- ✅ `TraceEnable Off`
- ✅ Cache navigateur optimisé

---

## 📊 Score de sécurité

| Critère | Note |
|---|---|
| HTTPS | ✅ A+ (HSTS, TLS 1.3) |
| Headers de sécurité | ✅ A |
| Backend (admin + API) | ✅ A |
| Authentification | ✅ A |
| CSRF | ✅ A |
| XSS | ✅ A (htmlspecialchars + CSP) |
| Injection SQL | ✅ N/A (pas de base de données) |
| Path Traversal | ✅ A |
| Rate Limiting | ✅ A |
| Logs | ✅ A |
| Backups | ⚠️ À configurer chez o2switch |

---

## ⚠️ Améliorations recommandées (à venir)

### 1. Sauvegardes automatiques
Activez les sauvegardes automatiques dans le panel o2switch (quotidiennes).

### 2. Monitoring
- Google Search Console (gratuit) pour détecter les anomalies
- Uptime monitoring (ex: UptimeRobot gratuit)

### 3. 2FA pour l'admin
Ajouter une authentification à deux facteurs pour `admin/admin.php`.

### 4. CSP report-uri
Ajouter un endpoint pour recevoir les violations CSP.

---

## 🚨 En cas de compromission

1. **Changer immédiatement** le mot de passe admin
2. **Vérifier** `admin.log` pour voir les actions
3. **Vérifier** `logs/contact.log` pour les spams
4. **Restaurer** depuis une sauvegarde o2switch
5. **Régénérer** le hash bcrypt via `admin/generate-password.php`
6. **Vérifier** qu'aucun fichier suspect n'a été uploadé

---

## 📞 Contact sécurité

Pour toute question : contact@allo-cle-auto.fr
