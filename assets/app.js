import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// import './styles/app.css';
import './js/personnage-form.js';
import { initCookieConsent } from './js/cookie-consent.js';
import { initGuideTinyMce } from './js/admin/tinymce-guides.js';

document.addEventListener('DOMContentLoaded', () => {
    initCookieConsent();
    initGuideTinyMce();
});

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
