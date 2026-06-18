const searchForm = document.querySelector('.search-form');

if (searchForm) {
    searchForm.addEventListener('submit', function (e) {

        const depart = document.getElementById('depart')?.value.trim();
        const destination = document.getElementById('destination')?.value.trim();
        const date = document.getElementById('real-date')?.value.trim();

        const errorSpan = document.getElementById('form-error');

        if (!depart || !destination || !date) {
            e.preventDefault();

            if (errorSpan) {
                errorSpan.classList.remove('hidden');
                errorSpan.textContent =
                    'Veuillez sélectionner une ville de départ, une ville de destination et une date.';
            }

        } else if (errorSpan) {
            errorSpan.classList.add('hidden');
        }
    });
}
