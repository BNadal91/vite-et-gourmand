/* Indicateur en direct du respect de la politique de mot de passe */
'use strict';
(() => {
    const input = document.getElementById('password');
    const rules = document.getElementById('pwd-rules');
    if (!input || !rules) return;
    const tests = {
        length: (v) => v.length >= 10,
        upper: (v) => /[A-Z]/.test(v),
        lower: (v) => /[a-z]/.test(v),
        digit: (v) => /\d/.test(v),
        special: (v) => /[^A-Za-z0-9]/.test(v),
    };
    input.addEventListener('input', () => {
        rules.querySelectorAll('li').forEach((li) => {
            const ok = tests[li.dataset.rule](input.value);
            li.classList.toggle('ok', ok);
            li.classList.toggle('ko', !ok);
        });
    });
})();
