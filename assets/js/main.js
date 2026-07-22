/* ============================================
   AUTO-KEY.FR — JavaScript
   ============================================ */
(function () {
  'use strict';

  /* ---- Sticky header ---- */
  const header = document.querySelector('.site-header');
  if (header) {
    const onScroll = () => {
      if (window.scrollY > 20) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---- Mobile nav ---- */
  const burger = document.querySelector('.burger');
  const mobileNav = document.querySelector('.mobile-nav');
  const overlay = document.querySelector('.mobile-overlay');
  function closeMobile() {
    if (!burger) return;
    burger.classList.remove('open');
    mobileNav && mobileNav.classList.remove('open');
    overlay && overlay.classList.remove('open');
    document.body.style.overflow = '';
  }
  if (burger && mobileNav) {
    burger.addEventListener('click', () => {
      const open = burger.classList.toggle('open');
      mobileNav.classList.toggle('open', open);
      overlay && overlay.classList.toggle('open', open);
      document.body.style.overflow = open ? 'hidden' : '';
    });
    overlay && overlay.addEventListener('click', closeMobile);
    mobileNav.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMobile));
  }

  /* ---- FAQ Accordion ---- */
  document.querySelectorAll('.faq-item').forEach(item => {
    const q = item.querySelector('.faq-question');
    const a = item.querySelector('.faq-answer');
    if (!q || !a) return;
    q.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      item.classList.toggle('open', !isOpen);
      a.style.maxHeight = isOpen ? '0' : a.scrollHeight + 'px';
    });
  });

  /* ---- Reveal on scroll ---- */
  const reveals = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && reveals.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    reveals.forEach(el => io.observe(el));
  } else {
    reveals.forEach(el => el.classList.add('visible'));
  }

  /* ---- Calculateur de prix ---- */
  const calc = document.getElementById('calculator');
  if (!calc) { console.log('[Auto-Key] Calculator not found'); }
  else {
    console.log('[Auto-Key] Calculator initialized');
    const basePrices = {
      'standard': 49,
      'puce': 69,
      'telecommande': 169,
      'mainlibre': 199
    };
    const state = {
      type: 'standard',
      domicile: false,
      distance: 0,
      ouverture: false
    };
    const distanceRate = 1.20; // €/km au-delà de 15km
    const freeRadius = 15;

    const optType = calc.querySelectorAll('[data-type]');
    const optDomicile = calc.querySelector('[data-toggle="domicile"]');
    const optOuverture = calc.querySelector('[data-toggle="ouverture"]');
    const addressInput = calc.querySelector('#calc-address');
    const distanceEl = calc.querySelector('[data-distance]');
    const amountEl = calc.querySelector('[data-amount]');
    const breakdownEl = calc.querySelector('[data-breakdown]');
    const resultSection = calc.querySelector('.calc-result');
    const addrWrap = calc.querySelector('.calc-address-wrap');

    function getTypeLabel(t) {
      return {
        'standard': 'Clé standard mécanique',
        'puce': 'Clé avec puce anti-démarrage',
        'telecommande': 'Clé avec télécommande',
        'mainlibre': 'Clé "Main Libre" / Carte'
      }[t] || 'Clé';
    }

    function update() {
      const base = basePrices[state.type] || 49;
      let extra = 0;
      const lines = [];
      lines.push(['Clé (' + getTypeLabel(state.type) + ')', base + '€']);
      if (state.ouverture) {
        extra += 30;
        lines.push(['Ouverture de porte', '+30€']);
      }
      let travel = 0;
      if (state.domicile && state.distance > freeRadius) {
        travel = Math.round((state.distance - freeRadius) * distanceRate);
        lines.push(['Déplacement (' + state.distance + ' km, au-delà de 15 km)', '+' + travel + '€']);
      } else if (state.domicile) {
        lines.push(['Déplacement (' + Math.max(state.distance, 0) + ' km)', 'Inclus']);
      }
      const total = base + extra + travel;
      if (amountEl) amountEl.textContent = total + '€';
      if (breakdownEl) {
        breakdownEl.innerHTML = lines.map(l => '<li><span>' + l[0] + '</span><span>' + l[1] + '</span></li>').join('');
      }
    }

    function selectType(el) {
      optType.forEach(o => o.classList.remove('selected'));
      el.classList.add('selected');
      state.type = el.dataset.type;
      update();
    }
    optType.forEach(o => o.addEventListener('click', () => selectType(o)));
    // Event delegation fallback in case individual listeners fail
    calc.addEventListener('click', (e) => {
      const opt = e.target.closest('[data-type]');
      if (opt) selectType(opt);
    });

    function toggleDomicile() {
      state.domicile = !state.domicile;
      if (optDomicile) optDomicile.classList.toggle('selected', state.domicile);
      if (addrWrap) addrWrap.style.display = state.domicile ? 'block' : 'none';
      if (state.domicile && addressInput && addressInput.value.trim().length >= 3) {
        geocodeAddress(addressInput.value.trim());
      } else {
        update();
      }
    }
    if (optDomicile) optDomicile.addEventListener('click', toggleDomicile);

    function toggleOuverture() {
      state.ouverture = !state.ouverture;
      if (optOuverture) optOuverture.classList.toggle('selected', state.ouverture);
      update();
    }
    if (optOuverture) optOuverture.addEventListener('click', toggleOuverture);

    // Géocodage réel via Nominatim (OpenStreetMap) — calcul distance depuis Scionzier
    const SCIONZIER = { lat: 46.0556, lon: 6.5806 };
    let geocodeTimer = null;

    function haversine(lat1, lon1, lat2, lon2) {
      const R = 6371;
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLon = (lon2 - lon1) * Math.PI / 180;
      const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
      return Math.round(R * c);
    }

    function geocodeAddress(query) {
      if (!query || query.trim().length < 3) {
        state.distance = 0;
        if (distanceEl) distanceEl.textContent = '0 km';
        update();
        return;
      }
      const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query + ', Haute-Savoie, France') + '&limit=1';
      fetch(url, { headers: { 'Accept-Language': 'fr' } })
        .then(r => r.json())
        .then(data => {
          if (data && data[0]) {
            const lat = parseFloat(data[0].lat);
            const lon = parseFloat(data[0].lon);
            state.distance = haversine(SCIONZIER.lat, SCIONZIER.lon, lat, lon);
            if (distanceEl) distanceEl.textContent = state.distance + ' km';
          } else {
            state.distance = 0;
            if (distanceEl) distanceEl.textContent = '— km';
          }
          update();
        })
        .catch(() => {
          state.distance = 0;
          if (distanceEl) distanceEl.textContent = '— km';
          update();
        });
    }

    if (addressInput) {
      addressInput.addEventListener('input', () => {
        clearTimeout(geocodeTimer);
        const v = addressInput.value.trim();
        if (v.length < 3) {
          state.distance = 0;
          if (distanceEl) distanceEl.textContent = '0 km';
          update();
          return;
        }
        if (distanceEl) distanceEl.textContent = '...';
        geocodeTimer = setTimeout(() => geocodeAddress(v), 600);
      });
    }

    update();
  }

  /* ---- Année footer ---- */
  document.querySelectorAll('[data-year]').forEach(el => el.textContent = new Date().getFullYear());

  /* ---- Active link ---- */
  const path = location.pathname;
  document.querySelectorAll('.nav a, .mobile-nav a').forEach(a => {
    const href = a.getAttribute('href');
    if (!href) return;
    if (href === path || (path === '/' && href.endsWith('index.html')) || (href.length > 1 && path.endsWith(href))) {
      a.classList.add('active');
    }
  });

  /* ---- Formulaire de contact ---- */
  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const ok = document.getElementById('form-success');
      const err = document.getElementById('form-error');
      const submitBtn = contactForm.querySelector('button[type="submit"]');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Envoi en cours...'; }
      const action = contactForm.getAttribute('action');
      if (!action || action.startsWith('mailto:')) {
        contactForm.style.display = 'none';
        if (ok) ok.classList.add('show');
        return;
      }
      fetch(action, { method: 'POST', body: new FormData(contactForm) })
        .then(r => {
          contactForm.style.display = 'none';
          if (ok) ok.classList.add('show');
          if (err) err.classList.remove('show');
        })
        .catch(() => {
          if (err) err.classList.add('show');
          if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Envoyer ma demande'; }
        });
    });
  }

  /* ---- Bandeau cookies RGPD ---- */
  const cookieBanner = document.getElementById('cookie-banner');
  if (cookieBanner && !localStorage.getItem('cookieConsent')) {
    cookieBanner.classList.add('show');
  }
  const btnAccept = document.getElementById('cookie-accept');
  const btnEssential = document.getElementById('cookie-essential');
  if (btnAccept) {
    btnAccept.addEventListener('click', function() {
      localStorage.setItem('cookieConsent', 'all');
      if (cookieBanner) cookieBanner.classList.remove('show');
    });
  }
  if (btnEssential) {
    btnEssential.addEventListener('click', function() {
      localStorage.setItem('cookieConsent', 'essential');
      if (cookieBanner) cookieBanner.classList.remove('show');
    });
  }

})();
