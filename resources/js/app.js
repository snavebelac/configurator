import Toastify from 'toastify-js';
import sort from '@alpinejs/sort';
import registerRichTextEditor from './editor';

window.addEventListener('toast', function (e) {
    Toastify(e.detail).showToast();
});

document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(sort);
    registerRichTextEditor(window.Alpine);

    // Off-canvas nav drawer state, shared between the topbar trigger and the
    // rail itself. A store rather than nested x-data because the rail is a
    // Livewire component and the trigger isn't — a store survives morphs and
    // doesn't care where either lives in the DOM.
    //
    // This is view state, not a preference: the expanded/collapsed choice is
    // persisted per user on the server, but whether the drawer happens to be
    // open right now is not worth a round trip.
    window.Alpine.store('nav', {
        open: false,

        toggle() {
            this.open ? this.close() : this.show();
        },

        show() {
            this.open = true;
            document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
        },

        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
        },
    });
});
