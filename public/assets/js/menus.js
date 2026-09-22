/* Filtres dynamiques de la vue globale des menus (sans rechargement de page) */
'use strict';
(() => {
    const form = document.getElementById('form-filtres');
    const liste = document.getElementById('liste-menus');
    const compteur = document.getElementById('compteur');
    const aucun = document.getElementById('aucun-menu');
    if (!form) return;

    let timer = null;
    let controller = null;

    const carte = (m) => `
        <div class="col-md-6 col-xl-4">
          <article class="card menu-card h-100">
            <img src="${escapeHtml(m.image || '/assets/img/menus/defaut.svg')}" class="card-img-top" alt="${escapeHtml(m.image_alt || '')}" loading="lazy" width="600" height="400">
            <div class="card-body d-flex flex-column">
              <div class="mb-2"><span class="badge badge-theme">${escapeHtml(m.theme)}</span> <span class="badge badge-regime">${escapeHtml(m.regime)}</span></div>
              <h3 class="card-title h5">${escapeHtml(m.titre)}</h3>
              <p class="card-text flex-grow-1">${escapeHtml(m.description)}</p>
              <ul class="list-unstyled menu-infos">
                <li><strong>Minimum :</strong> ${m.nombre_personne_minimum} personnes</li>
                <li><strong>Prix :</strong> ${formatPrix(m.prix_minimum)} pour ${m.nombre_personne_minimum} pers.
                  <span class="text-muted">(${formatPrix(m.prix_par_personne)}/pers.)</span></li>
              </ul>
              <a href="/menus/${Number(m.id)}" class="btn btn-primary mt-auto">Voir le détail<span class="visually-hidden"> du menu ${escapeHtml(m.titre)}</span></a>
            </div>
          </article>
        </div>`;

    async function actualiser() {
        const params = new URLSearchParams();
        new FormData(form).forEach((v, k) => { if (String(v).trim() !== '') params.append(k, v); });
        // Met à jour l'URL (lien partageable, bouton précédent) sans recharger la page
        history.replaceState(null, '', params.toString() ? `/menus?${params}` : '/menus');

        controller?.abort();
        controller = new AbortController();
        liste.setAttribute('aria-busy', 'true');
        try {
            const res = await fetch(`/api/menus?${params}`, { signal: controller.signal, headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(res.status);
            const menus = await res.json();
            liste.innerHTML = menus.map(carte).join('');
            compteur.textContent = `${menus.length} menu(s) trouvé(s)`;
            aucun.classList.toggle('d-none', menus.length > 0);
        } catch (e) {
            if (e.name !== 'AbortError') compteur.textContent = 'Impossible de charger les menus, merci de réessayer.';
        } finally {
            liste.removeAttribute('aria-busy');
        }
    }

    // Petit délai (debounce) pour ne pas envoyer une requête à chaque frappe
    form.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(actualiser, 300); });
    form.addEventListener('submit', (e) => { e.preventDefault(); actualiser(); });
    form.addEventListener('reset', () => setTimeout(actualiser, 0));
})();
