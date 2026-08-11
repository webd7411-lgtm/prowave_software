import '../scss/app.scss';

import * as bootstrap from 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';import 'sweetalert2/dist/sweetalert2.min.css';
import 'toastr/build/toastr.min.css';
import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';
import 'flatpickr/dist/flatpickr.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css';

import './modules/theme';
import './modules/sidebar';
import './modules/toast';
import './modules/confirm';
import './modules/forms';
import './modules/datatable';
import './modules/charts';
import './modules/notifications';
import './modules/init';

window.bootstrap = bootstrap;

import $ from 'jquery';
window.jQuery = window.$ = $;
