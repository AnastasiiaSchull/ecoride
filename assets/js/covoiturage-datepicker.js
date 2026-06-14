document.addEventListener('DOMContentLoaded', () => {

    // =========================
    // SAFE HELPERS
    // =========================
    const $ = (id) => document.getElementById(id);
    const $$ = (selector) => document.querySelector(selector);

    const safeAddEvent = (el, event, callback) => {
        if (el) el.addEventListener(event, callback);
    };

    // =========================
    // DATE INPUT + LABEL
    // =========================
    const dateInput = $('real-date');
    const dateLabel = $('date-label');

    safeAddEvent(dateInput, 'change', function () {
        if (!dateLabel) return;
        const date = new Date(this.value);
        if (isNaN(date)) return;
        dateLabel.textContent = date.toLocaleDateString('fr-FR');
    });

    // =========================
    // STATE
    // =========================
    let availableDates = [];

    // =========================
    // FETCH AVAILABLE DATES
    // =========================
    function updateAvailableDates(type, ville) {

        const otherVille =
            type === 'depart'
                ? $('destination')?.value
                : $('depart')?.value;

        if (!otherVille || !ville) return;

        fetch(`../pages/get_dates.php?type=${type}&ville=${encodeURIComponent(ville)}&other=${encodeURIComponent(otherVille)}`)
            .then(res => res.json())
            .then(dates => {
                availableDates = dates;
                showCalendarPopup();
            })
            .catch(err => console.error('get_dates error:', err));
    }

    // =========================
    // CALENDAR POPUP
    // =========================
    function showCalendarPopup() {

        const popup = $('calendar-popup');
        if (!popup) return;

        popup.innerHTML = '';

        availableDates.forEach(date => {
            const dayDiv = document.createElement('div');
            dayDiv.textContent = new Date(date).toLocaleDateString('fr-FR');
            dayDiv.dataset.value = date;

            popup.appendChild(dayDiv);
        });

        popup.style.display = 'block';

        popup.querySelectorAll('div').forEach(day => {
            day.addEventListener('click', () => {

                const selected = day.dataset.value;

                const realDate = $('real-date');
                const label = $('date-label');

                if (realDate) realDate.value = selected;
                if (label) label.textContent = new Date(selected).toLocaleDateString('fr-FR');

                popup.style.display = 'none';


                const departVille = $('depart')?.value;
                const arriveeVille = $('destination')?.value;

                if (!departVille || !arriveeVille) return;
                fetch(`../pages/get_places_personnes.php?ville_depart=${encodeURIComponent(departVille)}&ville_arrivee=${encodeURIComponent(arriveeVille)}&date=${encodeURIComponent(selected)}`)
                    .then(res => res.json())
                    .then(data => {

                        const max = parseInt(data.places_max ?? 0);
                        const input = document.querySelector('input[name="passager"]');
                        const infoDiv = $('places-info');

                        if (!input || !infoDiv) return;

                        if (parseInt(input.value) > max) {
                            input.value = max;
                            alert(`Le nombre maximum de passagers est ${max}`);
                        }

                        input.max = max;
                        infoDiv.textContent = `Nombre maximum de passagers : ${max}`;
                    })
                    .catch(err => console.error('places error:', err));
            });
        });
    }

    // =========================
    // CUSTOM DATE TRIGGER
    // =========================
    const customTrigger = $('custom-date-trigger');

    safeAddEvent(customTrigger, 'click', () => {

        const type = document.querySelector('input[name="type"]:checked')?.value;
        const departVille = $('depart')?.value;
        const destinationVille = $('destination')?.value;

        if (!type || !departVille || !destinationVille) {
            alert('Veuillez choisir les deux villes.');
            return;
        }


        const mainVille = type === 'depart' ? departVille : destinationVille;
        updateAvailableDates(type, mainVille);
    });

    // =========================
    // CLOSE POPUP OUTSIDE CLICK
    // =========================
    const popup = $('calendar-popup');


    document.addEventListener('click', (e) => {
        const trigger = $('custom-date-trigger');
        if (!popup || !trigger) return;

        if (!trigger.contains(e.target) && !popup.contains(e.target)) {
            popup.style.display = 'none';
        }
    });

    // =========================
    // DEPENDENT SELECTS
    // =========================
    const departSelect = $('depart');
    const destinationSelect = $('destination');

    // Depart → destinations
    safeAddEvent(departSelect, 'change', function () {

        const type = document.querySelector('input[name="type"]:checked')?.value;
        if (type !== 'depart') return;
        const departVille = this.value;
        if (!departVille) return;

        if (destinationSelect) {
            destinationSelect.innerHTML = '<option>Chargement...</option>';
        }

        fetch(`../pages/get_destinations.php?depart=${encodeURIComponent(departVille)}`)
            .then(res => res.json())
            .then(data => {

                if (!destinationSelect) return;

                destinationSelect.innerHTML = '<option value="">Choisir une ville</option>';

                data.forEach(ville => {
                    const option = document.createElement('option');
                    option.value = ville;
                    option.textContent = ville;
                    destinationSelect.appendChild(option);
                });
            })
            .catch(err => console.error('destinations error:', err));

        updateAvailableDates('depart', departVille);
    });

    // Destination → departs
    safeAddEvent(destinationSelect, 'change', function () {

        const type = document.querySelector('input[name="type"]:checked')?.value;
        if (type !== 'destination') return;

        const destinationVille = this.value;
        if (!destinationVille) return;

        if (departSelect) {
            departSelect.innerHTML = '<option>Chargement...</option>';
        }

        fetch(`../pages/get_departs.php?destination=${encodeURIComponent(destinationVille)}`)
            .then(res => res.json())
            .then(data => {

                if (!departSelect) return;

                departSelect.innerHTML = '<option value="">Choisir une ville</option>';

                data.forEach(ville => {
                    const option = document.createElement('option');
                    option.value = ville;
                    option.textContent = ville;
                    departSelect.appendChild(option);
                });
            })
            .catch(err => console.error('departs error:', err));

        updateAvailableDates('destination', destinationVille);
    });

});