# Auto-Key.fr — Site statique HTML/CSS/JS

Site développé from scratch pour Auto-Key, spécialiste du double de clé de voiture et du dépannage clé perdue sans double en Haute-Savoie (74) et en Savoie (73).

## Structure

```
AutoKey/
├── index.html                      # Accueil
├── services.html                   # Page services détaillée
├── tarifs.html                     # Calculateur de prix interactif
├── cle-perdue-haute-savoie.html    # Page clé perdue 74 (longue)
├── contact.html                    # Formulaire + carte
├── zone-intervention.html          # Hub zones d'intervention
├── a-propos.html                   # À propos (E-E-A-T)
├── mentions-legales.html           # Mentions légales
├── politique-cookies.html         # Politique cookies RGPD
├── blog.html                       # Index blog
├── 404.html                        # Page 404
├── blog/
│   ├── prix-double-cle-voiture-2026.html
│   ├── cle-voiture-perdue-que-faire.html
│   └── refaire-cle-renault-prix.html
├── villes/
│   ├── annecy.html
│   ├── annemasse.html
│   ├── thonon-les-bains.html
│   ├── cluses.html
│   ├── sallanches.html
│   ├── chambery.html
│   ├── bonneville.html
│   └── scionzier.html
├── assets/
│   ├── css/styles.css              # Design system complet
│   ├── js/main.js                  # Calculateur, menu, FAQ, reveal
│   └── img/                        # À remplir avec photos réelles
├── robots.txt
├── sitemap.xml
└── .htaccess                       # Réécritures, cache, sécurité
```

## SEO local intégré

- **Schema.org JSON-LD** (62 blocs valides) :
  - `LocalBusiness` / `AutoRepair` avec `areaServed` complet
  - `FAQPage` sur 6 pages (accueil, tarifs, clé perdue, pages villes, articles)
  - `BreadcrumbList` sur toutes les pages internes
  - `Service` + `OfferCatalog` + `Offer` (4 prix de clés)
  - `AggregateRating` + 3 `Review` (témoignages)
  - `ContactPage`, `AboutPage`, `Article`, `HowTo`, `ItemList`, `WebSite`
- **NAP cohérent** sur les 21 pages :
  - ☎ 07 46 57 17 03
  - ✉ contact@auto-key.fr
  - 📍 10 Rue du Collège, 74950 Scionzier
- **Sitemap XML** avec 19 URLs propres (sans .html)
- **.htaccess** : réécriture .html → /, HTTPS, gzip, cache, headers sécurité
- **robots.txt** avec référence au sitemap

## Quick wins Search Console ciblés

- Page `/tarifs` : titre optimisé "Tarif Double Clé Voiture 2026 : Calculateur Gratuit (49€ à 199€)" + FAQ schema
- Pages villes : Annecy, Annemasse, Thonon, Cluses, Sallanches, Chambéry (73), Bonneville, Scionzier
- Articles blog : prix, clé perdue, refaire clé Renault

## Déploiement

1. Téléverser tous les fichiers via FTP/SFTP sur o2switch
2. Ajouter les photos réelles dans `assets/img/` :
   - `hero-key.jpg` (800×600) — image hero accueil
   - `service-duplication.jpg` (640×400)
   - `service-perdue.jpg` (640×400)
   - `service-reparation.jpg` (640×400)
   - `service-diagnostic.jpg` (640×400)
   - `service-multimedia.jpg` (640×400)
   - `service-urgence.jpg` (640×400)
   - `og-image.jpg` (1200×630) — Open Graph
   - `logo.png` (500×500) — logo pour schema
3. Tester les redirections (.htaccess)
4. Soumettre le sitemap dans Google Search Console
5. Vérifier les schemas avec [Schema Markup Validator](https://validator.schema.org/)

## Performance

- CSS : 1 fichier minifié (~22 KB)
- JS : 1 fichier (~5 KB)
- Fonts : Google Fonts en preconnect + display:swap
- Images : lazy-load + dimensions explicites + WebP à venir
- Pas de framework, pas de jQuery, pas de plugin tiers

## Compatibilité navigateurs

- Chrome, Edge, Firefox, Safari (versions récentes)
- Mobile-first responsive
- Backdrop-filter supporté avec fallback

---

© 2026 Auto-Key.fr — Z.E shophub · SIRET 82399004900038