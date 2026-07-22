# Analyse complète & Plan d'optimisation — Auto-key.fr
**Date :** 22 juillet 2026
**Cible :** SEO local Haute-Savoie (74) + Performance + Conversion + Contenu
**Site :** WordPress + Astra + Rank Math SEO (hébergé o2switch)

---

## 1. ÉTAT DES LIEUX

### 1.1 Pages indexables (sitemap)
| URL | Type | Statut SEO |
|---|---|---|
| `/` | Accueil | OK mais H1 multiples |
| `/services-de-cle-et-multimedia-automobile/` | Page services | OK |
| `/tarif-reproduction-cle-voiture/` | Page tarif + calculateur | Excellent (USP) |
| `/cle-de-voiture-perdue-sans-double-depannage-rapide-74/` | Page longue | Excellent |
| `/contact/` | Contact | OK |
| `/mentions-legales/` | Légal | OK |
| `/cle-de-voiture-perdue/` | Article blog | OK |
| `/a-propos/` | Liée mais absente du sitemap | À vérifier (lien footer) |
| `/prix-double-cle-de-voiture/` | Redirige vers /tarif-reproduction-cle-voiture/ | Redirection à confirmer |

### 1.2 Technique
- Stack : WordPress + Astra 4.11.15 + Elementor/ElementsKit + Rank Math
- Cache : WP Fastest Cache (wpfc-minified détecté)
- Hébergement : o2switch (PowerBoost)
- robots.txt : OK
- Sitemap XML : OK (3 sous-sitemaps)
- HTTP/2 : à vérifier côté serveur
- HTTP headers : `Cache-Control: no-cache, no-store` sur HTML → OK pour page dynamique mais bloquant pour assets statiques si appliqué partout

### 1.3 Performance (page d'accueil)
- Poids HTML : **~170 KB** (correct)
- Scripts : **46** (trop nombreux → mauvais LCP/INP)
- Stylesheets : **47** (beaucoup)
- Inline styles : 7 blocs
- Images : 10 référencées, nombreuses en JPEG/PNG non WebP
- Lazyload : activé (50 occurrences) ✅

### 1.4 SEO local actuel
- Schema.org présent : `AutoRepair` + `Organization` + `Place` ✅
- Adresse dans le schema : **84 Allée Charles Pons, 74300 Cluses**
- Horaires : mar-ven 9-18h, sam 9-17h, lun/dim fermé ✅
- Pas de `LocalBusiness` enrichi (geo, areaServed, aggregateRating, hasOfferCatalog)
- Pas de FAQPage schema alors qu'il y a des FAQ sur 2 pages
- Pas de BreadcrumbList schema
- Pas de Review/AggregateRating schema alors qu'il y a des témoignages
- Pas de Service schema
- Pas de pages villes dédiées (Cluses, Annecy, Annemasse, Bonneville, Thonon…)

---

## 2. PROBLÈMES CRITIQUES À CORRIGER EN PRIORITÉ

### 2.1 🔴 Incohérences NAP (Name / Address / Phone) — URGENT
**Google sanctionne les NAP incohérents pour le SEO local.**

| Source | Adresse | Téléphone | Email |
|---|---|---|---|
| Schema.org (head) | 84 Allée Charles Pons, 74300 Cluses | 07.56.91.26.00 | — |
| Mentions légales | 10 Rue du Collège, 74950 Scionzier | 07 46 57 17 03 | contact@auto-key.fr |
| Page Contact | 10 Rue du Collège, 74950 Scionzier | +33.7.46.57.17.03 ( lien `tel:+33644660074` !) | info@auto-key.fr |
| Footer | — | +33.7.46.57.17.03 | — |

**Actions :**
1. Choisir UNE adresse officielle (probablement Scionzier = siège social, atelier mobile)
2. Choisir UN numéro (07 46 57 17 03)
3. Choisir UN email (contact@auto-key.fr)
4. Corriger le lien `tel:` de la page contact (pointe vers 06 44 66 00 74 — numéro différent !)
5. Mettre à jour le schema Rank Math avec la même adresse/telephone
6. Vérifier que la fiche Google Business Profile utilise EXACTEMENT les mêmes infos

### 2.2 🔴 H1 multiples sur l'accueil
- H1 actuel : "Duplication de Clés, Clés de Voiture Perdues, Diagnostic et Réparation Electronique Auto"
- Mais plusieurs H2 pourraient devoir être des H2/H3 correctement hiérarchisés
- Vérifier qu'**un seul H1** par page

### 2.3 🔴 Données structurées incomplètes
Ajouter via Rank Math ou snippet custom :
- `FAQPage` sur /tarif-reproduction-cle-voiture/ et /cle-de-voiture-perdue-sans-double-depannage-rapide-74/
- `BreadcrumbList` sur toutes les pages
- `AggregateRating` + `Review` (5 témoignages clients déjà présents)
- `Service` + `OfferCatalog` (modèles de clés à 49/69/169/199€)
- `LocalBusiness` avec `areaServed` = Haute-Savoie + liste des villes

### 2.4 🔴 Contenu local quasi inexistant
- Une seule page géolocalisée (`-74` dans l'URL)
- Aucune page ville dédiée → or les recherches "clé voiture perdue [ville]" sont hyper ciblées
- Pas de mention d'Annecy, Thonon-les-Bains, Évian, Saint-Julien-en-Genevois, La Roche-sur-Foron, Sallanches, Chamonix…

### 2.5 🔴 Page "À propos" manquante
- Lien dans le footer `/a-propos/` → pas dans le sitemap, à vérifier (404 ?)
- Google aime E-E-A-T (Expertise, Experience, Authoritativeness, Trust) → page A propos obligatoire avec photo, parcours, certifications

---

## 3. OPTIMISATIONS SEO LOCAL HAUTE-SAVOIE

### 3.1 Créer un cluster de pages villes (silos géographiques)
Chaque page doit être unique (pas de duplication) et contenir :
- Titre : "Clé de Voiture Perdue / Double Clé à [Ville] (74) — Auto-Key"
- H1 localisé
- Texte d'intro 150-200 mots unique
- Zone d'intervention spécifique
- Délai d'intervention depuis Scionzier/Cluses
- Témoignage si dispo
- FAQ locale
- CTA téléphone + calculateur
- Schema `LocalBusiness` avec `areaServed`

**Villes prioritaires (par population + distance) :**
1. Annecy (74 préfecture, ~130k habitants bassin)
2. Annemasse (bassin genevois, ~90k)
3. Thonon-les-Bains (bassin lémanique, ~80k)
4. Cluses (vallée de l'Arve, déjà cité)
5. Bonneville
6. Saint-Julien-en-Genevois
7. La Roche-sur-Foron
8. Sallanches
9. Chamonix-Mont-Blanc
10. Évian-les-Bains
11. Megève
12. Passy

### 3.2 Optimiser la page "Zone d'intervention"
Créer une page `/zone-intervention-haute-savoie/` avec carte interactive + liste des villes + délais estimés.

### 3.3 Google Business Profile
- Vérifier que la fiche est en service mobile (pas adresse physique si atelier mobile)
- Ajouter les services : Double clé, Clé perdue, Diagnostic électronique, Multimédia, Réparation clé
- Publier 1 post/semaine (astuce + témoignage + avant/après)
- Collecter **au moins 30 avis Google** (lien direct à demander aux clients)
- Photos : atelier mobile, clés avant/après, intervention sur site
- Q&R : reprendre les FAQ du site

### 3.4 Citations locales (NAP)
Inscrire sur : pagesjaunes.fr, mappy, yelp, 118000, hopneo.com, annuaire-mairie.fr, linkedin, facebook page. **NAP strictement identique partout.**

---

## 4. OPTIMISATIONS PERFORMANCE / CORE WEB VITALS

### 4.1 Scripts & CSS
- 46 scripts + 47 CSS = beaucoup trop. Identifier les plugins qui chargent sur toutes les pages
- Plugins suspects : ElementsKit (widget Instagram), MetForm (formulaire), Calendly (script tiers)
- **Action :** désactiver ElementsKit/Instagram hors page contact + Calendly hors page contact/RDV
- Utiliser **Perfmatters** ou **FlyingPress** pour décharger les scripts inutiles par page
- Ajouter `defer` / `async` sur tous les scripts non critiques
- Critical CSS inline (WP Rocket ou FlyingPress)

### 4.2 Images
- Convertir tous les JPEG/PNG en WebP/AVIF (plugin ShortPixel ou Imagify)
- Service picture/source avec fallback
- Dimensions explicites `width`/`height` sur toutes les images (évite CLS)
- Logo actuel : `data:image/svg+xml;base64` placeholder → vérifier que le LCP n'est pas une image SVG vide

### 4.3 Cache & HTTP
- Vérifier que les assets statiques (CSS/JS/fonts/images) ont `Cache-Control: public, max-age=31536000, immutable`
- Activer le préchargement des polices Google Fonts (`preconnect`, `font-display: swap`)
- Self-host les fonts Google (Better WordPress To Google Fonts) → économise 1 round-trip
- Activer HTTP/2 (o2switch OK par défaut) et Brotli/Gzip

### 4.4 Tiers
- Calendly : charger uniquement au clic (lazy embed)
- Google Tag Manager / Analytics : utiliser Partytown ou consent mode v2
- Instagram widget : remplacer par un script manuel ou un embed statique

### 4.5 LCP
- LCP element probablement le H1 ou la première image `pexels-photo-97075.jpeg`
- Précharger l'image LCP : `<link rel="preload" as="image" href="...">`
- Convertir en WebP + compresser

---

## 5. OPTIMISATIONS CONVERSION / DESIGN

### 5.1 CTA
- Bouton "URGENCES" en haut → ok mais le tel est en footer
- Ajouter **bouton téléphone flottant mobile** (sticky bottom)
- Ajouter **bouton WhatsApp** flottant
- Réduire les étapes du formulaire de contact (champs obligatoires minimum : nom + tel + message)
- Ajouter un **appel-tracké** (numéro Dynamic Number Insertion via call tracking) pour mesurer la conversion

### 5.2 Preuves sociales
- Les témoignages sont là mais pas de notation 5 étoiles visible (uniquement puces)
- Ajouter **étoiles SVG** + schema Review
- Ajouter **photos clients réelles** (avec autorisation) à côté des témoignages
- Afficher le nombre d'avis Google en badge
- Ajouter badges : "Garantie 12 mois", "Intervention < 1h", "TPE sur place", "Assurance acceptée"

### 5.3 Calculateur de prix
- Excellent USP, à mettre en avant dès l'accueil (au-dessus du fold)
- Ajouter un récapitulatif imprimable / envoyable par email
- Ajouter un bouton "Recevoir ce devis par SMS" → lead capture

### 5.4 Mobile
- Tester sticky header avec tel direct
- Vérifier la lisibilité du calculateur sur petit écran
- Taille min police 16px pour éviter zoom iOS

### 5.5 Form
- Page contact : formulaire Elementor (?) → ajouter champ "Marque & modèle du véhicule" + "Ville d'intervention" pour qualifier le lead
- Email transmettant directement vers un CRM simple (HubSpot Free ou Brevo)

---

## 6. OPTIMISATIONS CONTENU / SÉMANTIQUE

### 6.1 Articles de blog à créer (top requêtes potentielles Haute-Savoie)
1. "Perte clé voiture Renault Clio/Mégane : prix et solution 74"
2. "Refaire une clé de BMW sans le double : combien ça coûte ?"
3. "Clé de voiture prématurée : 5 signes qui doivent alerter"
4. "Assurance et clé perdue : comment se faire rembourser en Haute-Savoie"
5. "Double clé de Twingo 3 : le guide complet"
6. "Clé keyless (mains-libres) volée ou perdue : que faire ?"
7. "Reprogrammation calculateur auto : quand et pourquoi"
8. "Ouvrir sa voiture sans clé : légal et sans dégâts"
9. "Carte de démarrage Renault cassée : réparation ou remplacement"
10. "Coffre auto bloqué : diagnostic et solutions"

### 6.2 Maillage interne
- Chaque page ville → page mère "/zone-intervention-haute-savoie/"
- Chaque page ville → page tarif + page clé perdue
- Chaque article blog → page contact + calculateur
- Ajouter une section "Articles liés" en bas de chaque page

### 6.3 Cocons sémantiques
**Cocon "Perte de clé" :**
- Page mère : /cle-de-voiture-perdue-sans-double-depannage-rapide-74/
- Sous-pages par marque : Renault, Peugeot, BMW, Audi, VW, Mercedes
- Sous-pages par ville (voir 3.1)

**Cocon "Double de clé" :**
- Page mère : page d'accueil (section dédiée)
- Sous-pages par type de clé : standard, électronique, mains-libres, carte

### 6.4 Mots-clés à cibler (à confirmer avec Search Console)
- double clé voiture haute savoie
- clé voiture perdue cluses
- refaire clé voiture annecy
- clé de voiture perdue sans double 74
- tarif reproduction clé voiture
- dépannage clé auto à domicile
- serrurier automobile haute-savoie
- programmation clé auto
- carte renault perdue
- clé bmw perdue haute savoie

---

## 7. PLAN D'ACTION PRIORISÉ

### 🚀 Sprint 1 (J+1 — J+7) : Fondations
1. **Corriger NAP partout** (adresse unique + tel unique + email unique + corriger lien tel: de la page contact)
2. **Corriger le schema Rank Math** (adresse + tel cohérents)
3. **Ajouter FAQPage schema** sur les 2 pages FAQ
4. **Ajouter BreadcrumbList schema** (Rank Math > Settings > Breadcrumbs)
5. **Vérifier page /a-propos/** (existe-t-elle ? sinon la créer)
6. **Vérifier la redirection /prix-double-cle-de-voiture/**
7. **Ajouter un seul H1** sur l'accueil

### 🎯 Sprint 2 (J+8 — J+21) : SEO local
1. Créer les **5 pages villes prioritaires** (Annecy, Annemasse, Thonon, Cluses dédiée, Bonneville)
2. Créer page **/zone-intervention-haute-savoie/**
3. Optimiser Google Business Profile + demander 10 avis
4. Inscriptions annuaires locaux (PagesJaunes, Yelp, Facebook)
5. Ajouter schema `LocalBusiness` enrichi avec `areaServed` complet

### ⚡ Sprint 3 (J+22 — J+35) : Performance
1. Installer **FlyingPress** ou **Perfmatters** pour script management
2. Décharger ElementsKit + Calendly hors pages utiles
3. Convertir toutes les images en WebP (ShortPixel)
4. Self-host Google Fonts
5. Critical CSS + defer JS
6. Tester PageSpeed Insights Mobile (cible : 90+)

### 💰 Sprint 4 (J+36 — J+49) : Conversion
1. Sticky tel + WhatsApp sur mobile
2. Étoiles avis + schema Review
3. Formulaire enrichi (marque/modèle/ville)
4. Mise en avant du calculateur sur l'accueil
5. Badges de garantie

### ✍️ Sprint 5 (J+50 — J+90) : Contenu
1. 10 articles de blog (1 par semaine)
2. Pages par marque (Renault, Peugeot, BMW, Audi, VW)
3. Maillage interne systématique
4. Cocons sémantiques

---

## 8. DONNÉES SEARCH CONSOLE À DEMANDER

Pour affiner ce plan, j'ai besoin de :
- **Top 20 requêtes** (query) avec impressions + clics + position moyenne (3 derniers mois)
- **Top pages** par clics
- **Pays/régions** des impressions (filtre France + région Auvergne-Rhône-Alpes si possible)
- **Core Web Vitals** report (mobile + desktop)
- **Couverture d'indexation** (pages exclues / en erreur)
- **Mots-clés avec position 5-15** (quick wins à pousser en top 3)

Format idéal : export CSV ou capture de chaque section.

---

## 9. OUTILS RECOMMANDÉS

| Objectif | Outil |
|---|---|
| Cache + optimisation | FlyingPress ou WP Rocket |
| Script management | Perfmatters ou FlyingPress |
| Images WebP | ShortPixel ou Imagify |
| SEO | Rank Math PRO (déjà installé) |
| Schema | Rank Math Schema Generator ou Schema Pro |
| Suivi position | Ubersuggest ou SEObserver (ou Search Console) |
| Avis Google | Grade.us ou widget Trustindex |
| Call tracking | Modula ou DialogBoost |

---

*Préparé par opencode — en attente des données Search Console pour affiner les quick wins.*