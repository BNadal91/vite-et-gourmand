/* Tableau de bord administrateur : graphique Chart.js alimenté par MongoDB via /api/admin/stats */
'use strict';
(() => {
    const form = document.getElementById('form-stats');
    if (!form) return;
    let chart = null;

    async function charger() {
        const params = new URLSearchParams();
        new FormData(form).forEach((v, k) => { if (v) params.append(k, v); });
        const tbody = document.getElementById('table-stats');
        try {
            const res = await fetch(`/api/admin/stats?${params}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (!res.ok) throw new Error(data.erreur || 'Erreur');
            document.getElementById('total-commandes').textContent = data.total.nombre_commandes;
            document.getElementById('total-ca').textContent = formatPrix(data.total.chiffre_affaires);
            tbody.innerHTML = data.menus.length
                ? data.menus.map((m) => `<tr><td>${escapeHtml(m.menu)}</td><td class="text-end">${m.nombre_commandes}</td><td class="text-end">${formatPrix(m.chiffre_affaires)}</td></tr>`).join('')
                : '<tr><td colspan="3">Aucune commande sur cette période.</td></tr>';
            const labels = data.menus.map((m) => m.menu);
            const valeurs = data.menus.map((m) => m.nombre_commandes);
            if (chart) {
                chart.data.labels = labels;
                chart.data.datasets[0].data = valeurs;
                chart.update();
            } else if (window.Chart) {
                chart = new Chart(document.getElementById('chart-commandes'), {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Commandes', data: valeurs, backgroundColor: '#7A1F3D', borderRadius: 6 }] },
                    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } },
                });
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="3">${escapeHtml(e.message)}</td></tr>`;
        }
    }
    form.addEventListener('change', charger);
    form.addEventListener('reset', () => setTimeout(charger, 0));
    charger();
})();
