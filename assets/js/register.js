document.addEventListener('DOMContentLoaded', () => {

    const conducteur = document.getElementById('roleConducteur');
    const carFields = document.getElementById('carFields');

    if (!conducteur || !carFields) return;

    const update = () => {
        carFields.style.display = conducteur.checked ? 'block' : 'none';
    };

    conducteur.addEventListener('change', update);
    update();
});