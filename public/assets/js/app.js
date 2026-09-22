/* Vite & Gourmand — scripts communs */
'use strict';

/** Jeton CSRF lu dans la balise <meta> (à joindre aux requêtes fetch POST). */
window.csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/** Formatage monétaire français (1 234,50 €). */
window.formatPrix = (n) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(n);

/** Échappement HTML côté client (les données injectées dans le DOM ne sont jamais interprétées). */
window.escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

document.addEventListener('DOMContentLoaded', () => {
    // Demande de confirmation avant une action destructrice (annulation, suppression)
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
    // Place le focus sur le résumé des erreurs pour les lecteurs d'écran (RGAA 11.10)
    document.getElementById('resume-erreurs')?.focus();
});
