// Preloader : recherche de technicien le plus proche
(function() {
  if (window.location.pathname.indexOf('/admin/') === 0) return;

  // Trouver le preloader existant
  function init() {
    const preloader = document.getElementById('preloader');
    if (!preloader) return;

    // Trouver les elements
    const techDot = preloader.querySelector('.tech-dot');
    const connector = preloader.querySelector('.connector-line');
    const label = preloader.querySelector('.preloader-label');
    const distance = preloader.querySelector('.preloader-distance');
    if (!techDot || !connector || !label) return;

    // Forcer le contenu de l'emoji
    if (!techDot.textContent.trim()) {
      techDot.textContent = '\uD83D\uDE97'; // voiture
    }

    // Sequence d'animation
    setTimeout(() => {
      techDot.classList.add('visible');
    }, 400);

    setTimeout(() => {
      connector.classList.add('drawn');
    }, 700);

    setTimeout(() => {
      if (label) label.textContent = 'Localisation en cours...';
    }, 200);

    setTimeout(() => {
      if (label) label.textContent = 'Recherche des techniciens disponibles...';
    }, 900);

    setTimeout(() => {
      if (label) label.textContent = 'V\u00e9rification des distances...';
    }, 1600);

    setTimeout(() => {
      techDot.classList.add('arrived');
    }, 2000);

    setTimeout(() => {
      if (label) {
        label.innerHTML = '<span class="check">\u2713</span> Technicien trouv\u00e9 \u00e0 proximit\u00e9';
        label.classList.add('success');
      }
    }, 2300);

    // Distance aleatoire
    const km = Math.floor(Math.random() * 20) + 5;
    setTimeout(() => {
      if (distance) distance.textContent = 'Le plus proche : ~' + km + ' km de chez vous';
    }, 2400);

    // Fermer le preloader apres 2.8 secondes
    setTimeout(() => {
      preloader.classList.add('hidden');
      setTimeout(() => {
        preloader.remove();
      }, 600);
    }, 2800);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
