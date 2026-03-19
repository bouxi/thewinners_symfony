// assets/js/personnage-form.js

/**
 * Initialise un formulaire de personnage avec dépendance Classe -> Spécialisation.
 *
 * Le script cherche tous les blocs ayant :
 *   data-personnage-form
 *
 * Et lit :
 *   data-specs-url-template
 *   data-class-selector
 *   data-spec-selector
 *
 * Exemple d'URL template :
 *   /api/wow/specs?class=__CLASS__
 */
function initPersonnageForms() {
    const forms = document.querySelectorAll('[data-personnage-form]');

    forms.forEach((container) => {
        const specsUrlTemplate = container.dataset.specsUrlTemplate;
        const classSelector = container.dataset.classSelector;
        const specSelector = container.dataset.specSelector;

        if (!specsUrlTemplate || !classSelector || !specSelector) {
            return;
        }

        const classSelect = document.querySelector(classSelector);
        const specSelect = document.querySelector(specSelector);

        if (!classSelect || !specSelect) {
            return;
        }

        function resetSpecs() {
            specSelect.innerHTML = '<option value="">— Choisir d’abord une classe —</option>';
            specSelect.disabled = true;
        }

        function fillSpecs(specs, selectedSpec = null) {
            specSelect.innerHTML = '<option value="">— Choisir une spécialisation —</option>';

            specs.forEach((spec) => {
                const option = document.createElement('option');
                option.value = spec;
                option.textContent = spec;

                if (selectedSpec && selectedSpec === spec) {
                    option.selected = true;
                }

                specSelect.appendChild(option);
            });

            specSelect.disabled = false;
        }

        async function loadSpecs(className, selectedSpec = null) {
            if (!className) {
                resetSpecs();
                return;
            }

            try {
                const url = specsUrlTemplate.replace('__CLASS__', encodeURIComponent(className));
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    resetSpecs();
                    return;
                }

                const data = await response.json();
                const specs = Array.isArray(data.specs) ? data.specs : [];

                if (!specs.length) {
                    resetSpecs();
                    return;
                }

                fillSpecs(specs, selectedSpec);
            } catch (error) {
                console.error('Erreur lors du chargement des spécialisations :', error);
                resetSpecs();
            }
        }

        classSelect.addEventListener('change', () => {
            loadSpecs(classSelect.value, null);
        });

        // ✅ Initialisation au chargement (édition ou retour validation)
        const initialClass = classSelect.value;
        const initialSpec = specSelect.value;

        if (initialClass) {
            loadSpecs(initialClass, initialSpec);
        } else {
            resetSpecs();
        }
    });
}

// AssetMapper / Turbo / chargement classique
document.addEventListener('DOMContentLoaded', initPersonnageForms);
document.addEventListener('turbo:load', initPersonnageForms);