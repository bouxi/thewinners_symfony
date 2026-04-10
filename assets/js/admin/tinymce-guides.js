/**
 * Initialise TinyMCE sur les zones de texte marquées avec .js-rich-editor.
 *
 * Cette version :
 * - ne fait rien si aucun champ n'est présent sur la page
 * - charge TinyMCE dynamiquement depuis l'URL locale self-hosted
 * - évite les doubles initialisations
 * - affiche des logs utiles pour le debug
 */
async function loadTinyMceScript() {
    // Si TinyMCE est déjà présent dans la page, on ne recharge pas le script.
    if (typeof window.tinymce !== 'undefined') {
        return window.tinymce;
    }

    // Évite d'injecter plusieurs fois le même <script>.
    const existingScript = document.querySelector('script[data-tinymce-loader="true"]');

    if (existingScript) {
        return new Promise((resolve, reject) => {
            existingScript.addEventListener('load', () => resolve(window.tinymce));
            existingScript.addEventListener('error', () => reject(new Error('Le script TinyMCE existant a échoué au chargement.')));
        });
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');

        // IMPORTANT :
        // ce chemin suppose que TinyMCE est réellement accessible publiquement ici :
        // public/vendor/tinymce/tinymce.min.js
        script.src = '/vendor/tinymce/tinymce.min.js';
        script.async = true;
        script.dataset.tinymceLoader = 'true';

        script.onload = () => {
            if (typeof window.tinymce === 'undefined') {
                reject(new Error('TinyMCE semble chargé, mais window.tinymce est introuvable.'));
                return;
            }

            resolve(window.tinymce);
        };

        script.onerror = () => {
            reject(new Error('Impossible de charger TinyMCE depuis /vendor/tinymce/tinymce.min.js'));
        };

        document.head.appendChild(script);
    });
}

export async function initGuideTinyMce() {
    const textarea = document.querySelector('textarea.js-rich-editor');

    // On ne fait rien si la page ne contient pas l'éditeur.
    if (!textarea) {
        return;
    }

    try {
        const tinymce = await loadTinyMceScript();

        // Si une instance existe déjà sur ce textarea, on la supprime proprement.
        const existingEditor = tinymce.get(textarea.id);
        if (existingEditor) {
            existingEditor.remove();
        }

        tinymce.init({
            selector: 'textarea.js-rich-editor',
            license_key: 'gpl',
            height: 500,
            menubar: true,
            branding: false,
            promotion: false,
            convert_urls: false,
            browser_spellcheck: true,
            contextmenu: false,
            plugins: 'lists link image table code fullscreen preview',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | code preview fullscreen'
        });

        console.log('TinyMCE initialisé avec succès.');
    } catch (error) {
        console.error('Erreur d’initialisation TinyMCE :', error);
    }
}