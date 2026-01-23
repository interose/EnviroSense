import './bootstrap.js';

import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
    Turbo.session.drive = false;
}
Turbo.setProgressBarDelay(100);