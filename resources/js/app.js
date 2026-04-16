import './bootstrap';
import Toastify from 'toastify-js';
import sort from '@alpinejs/sort';

window.addEventListener('toast', function (e) {
    Toastify(e.detail).showToast();
});

document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(sort);
});
