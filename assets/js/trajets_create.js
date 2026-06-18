document.addEventListener('DOMContentLoaded', () => {

    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr('.js-datetime', {
        enableTime: true,
        time_24hr: true,
        dateFormat: 'Y-m-d H:i',
        minDate: 'today'
    });

});
