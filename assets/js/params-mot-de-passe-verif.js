document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#parametres-utilisateur form');
  if (!form) return;

  const oldInput = form.querySelector('input[name="old_password"]');
  const csrf     = form.querySelector('input[name="csrf"]');

  // Zone d’erreur inline (optionnel)
  const errBox = document.createElement('div');
  errBox.style.cssText = 'color:#f44336;margin:8px 0 0;font-weight:600;';
  oldInput.insertAdjacentElement('afterend', errBox);

  form.addEventListener('submit', async (e) => {
    // Si l’utilisateur ne demande pas de changer son mot de passe → pas de vérif AJAX
    const newVal = (form.querySelector('input[name="new_password"]')?.value || '').trim();
    const oldVal = (oldInput?.value || '').trim();
    if (!oldVal && !newVal) return; // pas de changement de MDP

    e.preventDefault(); // on bloque l’envoi pendant la vérif

    // Vérif immédiatement les deux champs requis
    if (!oldVal || !newVal) {
      errBox.textContent = 'Veuillez remplir les deux champs de mot de passe.';
      oldInput.focus();
      return;
    }

    try {
      const fd = new FormData();
      fd.append('csrf', csrf.value);
      fd.append('old_password', oldVal);

      const res = await fetch('/parametres/check-mdp', { method: 'POST', body: fd, credentials: 'same-origin' });
      const json = await res.json();

      if (!json.ok) {
        errBox.textContent = json.error || 'Vérification impossible.';
        oldInput.focus();
        oldInput.select();
        return;
      }

      // OK → on soumet réellement le formulaire
      errBox.textContent = '';
      form.submit();

    } catch (err) {
      errBox.textContent = 'Erreur réseau. Veuillez réessayer.';
    }
  });
});