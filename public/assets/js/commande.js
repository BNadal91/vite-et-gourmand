/* Formulaire de commande : calcul du prix détaillé en direct via l'API /api/prix */
'use strict';
(() => {
    const form = document.getElementById('form-commande');
    if (!form) return;
    const $ = (id) => document.getElementById(id);
    const champs = ['menu_id', 'nombre_personne', 'adresse_livraison', 'code_postal_livraison', 'ville_livraison', 'distance_km'];
    let timer = null;

    // À la sélection d'un menu, le nombre de personnes est initialisé au minimum du menu
    $('menu_id')?.addEventListener('change', (e) => {
        const opt = e.target.selectedOptions[0];
        const min = Number(opt?.dataset.min || 1);
        const nb = $('nombre_personne');
        nb.min = min;
        if (!nb.value || Number(nb.value) < min) nb.value = min;
    });

    async function calculer() {
        if (!$('menu_id').value) return;
        const data = new FormData();
        champs.forEach((c) => { if ($(c)) data.append(c, $(c).value); });
        try {
            const res = await fetch('/api/prix', { method: 'POST', body: data, headers: { 'X-CSRF-Token': csrfToken(), Accept: 'application/json' } });
            if (!res.ok) return;
            const p = await res.json();
            $('nombre_personne').min = p.nombre_minimum;
            $('r-nb').textContent = Math.max(Number($('nombre_personne').value) || 0, p.nombre_minimum);
            $('r-ppp').textContent = formatPrix(p.prix_par_personne);
            $('r-brut').textContent = formatPrix(p.prix_brut);
            $('r-reduction').textContent = p.reduction > 0 ? `− ${formatPrix(p.reduction)}` : '—';
            $('r-livraison').textContent = p.prix_livraison > 0 ? formatPrix(p.prix_livraison) : 'Offerte';
            $('r-km').textContent = p.distance_km > 0 ? `(${String(p.distance_km).replace('.', ',')} km)` : '';
            $('r-total').textContent = formatPrix(p.prix_total);
            const infos = [];
            if (!p.nombre_valide) infos.push(`⚠️ Ce menu se commande pour ${p.nombre_minimum} personnes minimum.`);
            if (p.reduction === 0) infos.push(`💡 −10 % à partir de ${p.seuil_reduction} personnes.`);
            if (p.source_distance === 'estimation') infos.push('Distance estimée (itinéraire indisponible).');
            $('r-info').textContent = infos.join(' ');
            // Adresse non géolocalisée hors Bordeaux : on demande la distance au client
            $('bloc-distance')?.classList.toggle('d-none', p.source_distance !== 'saisie' || p.prix_livraison === 0 && $('ville_livraison').value.trim().toLowerCase() === 'bordeaux');
            const cond = $('recap-conditions');
            if (cond) { cond.textContent = `⚠️ Conditions du menu : ${p.conditions}`; cond.classList.remove('d-none'); }
        } catch (e) { /* le serveur recalculera le prix à la validation */ }
    }

    form.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(calculer, 400); });
    form.addEventListener('change', () => { clearTimeout(timer); timer = setTimeout(calculer, 100); });
    if ($('menu_id').value) {
        const nb = $('nombre_personne');
        const opt = $('menu_id').selectedOptions?.[0];
        if (opt && !nb.value) nb.value = opt.dataset.min || '';
        calculer();
    }
})();
