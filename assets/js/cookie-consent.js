export function initCookieConsent() {
    console.log('🍪 initCookieConsent chargé');

    const banner = document.getElementById('cookie-banner');
    const manageBtn = document.getElementById('manage-cookies');

    // ✅ 1. Gestion bouton "Gérer mes cookies" (IMPORTANT AVANT return)
    if (manageBtn) {
        manageBtn.addEventListener('click', () => {
            console.log('👉 manage cookies click');

            if (banner) {
                banner.hidden = false;
                banner.classList.add('is-visible');
            }
        });
    }

    // ⚠️ Si bannière absente, on stop ici MAIS le bouton est déjà géré
    if (!banner) {
        console.log('❌ Banner introuvable');
        return;
    }

    const showBanner = () => {
        banner.hidden = false;
        banner.classList.add('is-visible');
    };

    const hideBanner = () => {
        banner.classList.remove('is-visible');
        banner.hidden = true;
    };

    const consentCookie = document.cookie
        .split('; ')
        .find(row => row.startsWith('tw_cookie_consent='));

    // ✅ 2. Vérification serveur (version + état)
    fetch('/consent/status', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
        .then(res => res.json())
        .then(data => {
            if (data.mustAskConsent) {
                console.log('🔁 Consent requis (version ou refus)');
                showBanner();
            } else if (!consentCookie) {
                console.log('👤 Visiteur sans cookie');
                showBanner();
            }
        })
        .catch(() => {
            console.log('⚠️ API consent KO → fallback');
            if (!consentCookie) {
                showBanner();
            }
        });

    const buttons = banner.querySelectorAll('[data-cookie-action]');
    const url = banner.dataset.consentUrl;

    if (!url) {
        console.error('❌ URL consent absente');
        return;
    }

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            console.log('👉 click détecté');

            const action = button.dataset.cookieAction;
            const choice = action === 'accept' ? 'accepted' : 'rejected';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ choice }),
                });

                if (!response.ok) {
                    throw new Error('Erreur consentement');
                }

                console.log('✅ consent enregistré');

                hideBanner();

            } catch (error) {
                console.error('❌ erreur fetch', error);
            }
        });
    });
}