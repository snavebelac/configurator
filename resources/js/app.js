import './bootstrap';
import Toastify from 'toastify-js';

window.addEventListener('toast',function(e){
    Toastify(e.detail).showToast();
});
