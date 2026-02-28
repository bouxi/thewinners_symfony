(() => {
  const data = window.WOTLK_CLASSES || {};
  const old = window.OLD || {};

  const classSelect = document.getElementById('classSelect');
  const specSelect = document.getElementById('specSelect');
  if (!classSelect || !specSelect) return;

  function fillSpecs(className, selectedSpec = '') {
    specSelect.innerHTML = '<option value="">Choisir une spécialisation</option>';

    const specs = data[className] || [];
    specs.forEach((spec) => {
      const opt = document.createElement('option');
      opt.value = spec;
      opt.textContent = spec;
      if (spec === selectedSpec) opt.selected = true;
      specSelect.appendChild(opt);
    });
  }

  // Quand on change de classe -> update des spé
  classSelect.addEventListener('change', () => {
    fillSpecs(classSelect.value, '');
  });

  // Au chargement : si old.class existe, on remplit + sélectionne old.specialization
  if (old.class) {
    fillSpecs(old.class, old.specialization || '');
  }
})();
