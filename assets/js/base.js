import Swal from 'sweetalert2';

//------------POP UP SWAL2 MESSAGES FLASHES ------------------------

export function initFlashes() {
    const flashes = document.querySelectorAll('.flash-message');

    flashes.forEach((el) => {
        const type = el.dataset.type;
        const message = el.dataset.message;

        Swal.fire({
            icon: type === 'error' ? 'error' : type,
            title: type.charAt(0).toUpperCase() + type.slice(1),
            text: message,
            confirmButtonColor: '#3085d6'
        });

        el.remove();
    });
}

//-------------POP UP SWAL2 QUITTER ------------------------------

const logoutBtn = document.getElementById('logoutBtn');

if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();

        Swal.fire({
            title: "Déconnexion",
            icon: "warning",
            width: 330,
            padding: "1.5rem",
            showCancelButton: true,
            confirmButtonText: "Oui, quitter",
            cancelButtonText: "Annuler",
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            customClass: {
                popup: 'swal-small-popup'
            }
        }).then((result) => {

            if (result.isConfirmed) {
                window.location.href = logoutBtn.href;
            }

        });
    });
}

//--------------BURGER BUTTON----------------------------------
document.addEventListener('DOMContentLoaded', () => {

    const burger = document.getElementById('burgerBtn');
    const menu = document.getElementById('topMenu');

    if (!burger || !menu) return;

    // 🚫 désactive les animations AU CHARGEMENT UNIQUEMENT
    menu.style.transition = 'none';

    // applique l'état sauvegardé
    if (window.innerWidth >= 992) {

        if (localStorage.getItem('menuOpen') === 'true') {
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }

    } else {
        menu.classList.add('hidden');
    }

    // ♻️ réactive les transitions après 1 frame
    requestAnimationFrame(() => {
        menu.style.transition = '';
    });

    // click burger normal
    burger.addEventListener('click', () => {

        menu.classList.toggle('hidden');

        if (window.innerWidth >= 992) {
            localStorage.setItem(
                'menuOpen',
                String(!menu.classList.contains('hidden'))
            );
        }

    });

});

//---------GESTION DE la COOKIE BANNER-------------------------

document.addEventListener('DOMContentLoaded', () => {
    const banner = document.getElementById('cookie-banner');

    if (!banner) {
        return;
    }

    const consent = localStorage.getItem('cookieConsent');

    if (!consent) {
        banner.classList.add('show');
    }
});
    
//--------------------------------------------------------------