  document.addEventListener('DOMContentLoaded', function () {

      const dateInput = document.getElementById('real-date');
      const dateLabel = document.getElementById('date-label');

      dateInput.addEventListener('change', function () {
        const date = new Date(this.value);
        const formatted = date.toLocaleDateString('fr-FR'); // formate comme 01/07/2025
        dateLabel.textContent = formatted;
      });


      let availableDates = [];

      function updateAvailableDates(type, ville) {
        const otherVille = (type === 'depart')
          ? document.getElementById('destination').value
          : document.getElementById('depart').value;

        if (!otherVille) return;

        fetch(`../pages/get_dates.php?type=${type}&ville=${encodeURIComponent(ville)}&other=${encodeURIComponent(otherVille)}`)
          .then(response => response.json())
          .then(dates => {
            availableDates = dates;
            showCalendarPopup();
          });
      }


      function showCalendarPopup() {
        const popup = document.getElementById('calendar-popup');
        popup.innerHTML = '';

        availableDates.forEach(date => {
          const dayDiv = document.createElement('div');
          dayDiv.textContent = new Date(date).toLocaleDateString('fr-FR');
          dayDiv.dataset.value = date;
          popup.appendChild(dayDiv);
        });

        popup.style.display = 'block';
        // 👇 Добавляем обработчики кликов на каждый день
        popup.querySelectorAll('div').forEach(day => {
          day.addEventListener('click', () => {
            const selected = day.dataset.value;
            document.getElementById('real-date').value = selected;
            document.getElementById('date-label').textContent = new Date(selected).toLocaleDateString('fr-FR');
            popup.style.display = 'none';

            const departVille = document.getElementById('depart').value;
            const arriveeVille = document.getElementById('destination').value;

            fetch(`../pages/get_places_personnes.php?ville_depart=${encodeURIComponent(departVille)}&ville_arrivee=${encodeURIComponent(arriveeVille)}&date=${encodeURIComponent(selected)}`)
              // .then(res => res.json())
              // .then(data => {
              .then(res => res.text()) // сначала просто текст
              .then(text => {
                console.log(text); // смотри в консоли, что реально вернулось
                const data = JSON.parse(text); // потом уже парсишь
                const max = data.places_max;
                const input = document.querySelector('input[name="passager"]');
                const infoDiv = document.getElementById('places-info');

                if (parseInt(input.value) > max) {
                  alert(`Le nombre maximum de passagers est ${max}`);
                  input.value = max;
                }

                input.max = max;

                // показываем красивую подсказку
                infoDiv.textContent = `Nombre maximum de passagers : ${max}`;

              });
          });
        });
      }

      // при клике на "Aujourd'hui"
      document.getElementById('custom-date-trigger').addEventListener('click', () => {
        const type = document.querySelector('input[name="type"]:checked').value;
        const departVille = document.getElementById('depart').value;
        const destinationVille = document.getElementById('destination').value;

        // убедимся, что выбраны обе стороны
        if ((type === 'depart' && departVille && destinationVille) ||
          (type === 'destination' && destinationVille && departVille)) {
          const mainVille = type === 'depart' ? departVille : destinationVille;
          updateAvailableDates(type, mainVille);
        } else {
          alert('Veuillez choisir les deux villes.');
        }
      });

      // при клике вне календаря — закрыть
      document.addEventListener('click', function (e) {
        const trigger = document.getElementById('custom-date-trigger');
        const popup = document.getElementById('calendar-popup');
        if (!trigger.contains(e.target)) {
          popup.style.display = 'none';
        }
      });

      const departSelect = document.getElementById('depart');
      const destinationSelect = document.getElementById('destination');
      const radios = document.getElementsByName('type');

      // si sélectionné Départ - chargeons destinations
      departSelect.addEventListener('change', function () {
        if (document.querySelector('input[name="type"]:checked').value === 'depart') {
          const departVille = this.value;
          if (!departVille) return;

          destinationSelect.innerHTML = '<option value="">Chargement...</option>';

          fetch('../pages/get_destinations.php?depart=' + encodeURIComponent(departVille))
            .then(response => response.json())
            .then(data => {
              destinationSelect.innerHTML = '<option value="">Choisir une ville</option>';
              data.forEach(ville => {
                const option = document.createElement('option');
                option.value = ville;
                option.textContent = ville;
                destinationSelect.appendChild(option);
              });
            });
          updateAvailableDates('depart', departVille);
        }
      });

      // si sélectionné Destination - chargeons departs
      destinationSelect.addEventListener('change', function () {
        if (document.querySelector('input[name="type"]:checked').value === 'destination') {
          const destinationVille = this.value;
          if (!destinationVille) return;

          departSelect.innerHTML = '<option value="">Chargement...</option>';

          fetch('../pages/get_departs.php?destination=' + encodeURIComponent(destinationVille))
            .then(response => response.json())
            .then(data => {
              departSelect.innerHTML = '<option value="">Choisir une ville</option>';
              data.forEach(ville => {
                const option = document.createElement('option');
                option.value = ville;
                option.textContent = ville;
                departSelect.appendChild(option);
              });
            });
          updateAvailableDates('destination', destinationVille);
        }
      });

    });