 document.querySelector('.search-form').addEventListener('submit', function (e) {
        const depart = document.getElementById('depart').value.trim();
        const destination = document.getElementById('destination').value.trim();
        const date = document.getElementById('real-date').value.trim();

        const errorSpan = document.getElementById('form-error');

        if (!depart || !destination || !date) {
          e.preventDefault(); // empêche l'envoi du formulaire si les champs sont vides
          errorSpan.classList.remove('hidden');
          errorSpan.textContent = 'Veuillez sélectionner une ville de départ, une ville de destination et une date.';
        } else {
          errorSpan.classList.add('hidden'); // masque le message si tout est correct
        }
      });
