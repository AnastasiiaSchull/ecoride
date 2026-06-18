document.addEventListener('DOMContentLoaded', () => {
    const banner = document.getElementById('cookie-banner');

    if (!banner) return;

    if (localStorage.getItem('cookieConsent')) {
        banner.remove();
        return;
    }

    document.getElementById('accept-cookies')?.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'accepted');
        banner.remove();
    });

    document.getElementById('refuse-cookies')?.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'refused');
        banner.remove();
    });
});