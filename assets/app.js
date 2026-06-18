import './bootstrap';

import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';


import './js/admin_dashboard.js';
import './js/base.js';
import './js/cookies.js';
import './js/covoiturage-datepicker.js';
import './js/form-error-checker.js';
import './js/register.js';
import './js/toggle-selects.js';
import './js/trajets_create.js';




import './styles/app.css';
import './base_twig/base.css';
import './styles/css/cookie.css';


import Swal from 'sweetalert2';
window.Swal = Swal;

import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
window.flatpickr = flatpickr;

import { initFlashes } from './js/base.js';

document.addEventListener('DOMContentLoaded', () => {
    initFlashes();
});




