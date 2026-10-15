/*
 * Alpine CSP build: works with the strict Content-Security-Policy (no 'unsafe-eval').
 * Inline expressions stay simple (toggles, assignments, method calls); anything touching
 * browser globals lives in an Alpine.data() component registered below.
 */
import Alpine from '@alpinejs/csp';

/* "Around me" button: fills hidden lat/lng fields from the browser geolocation, then submits. */
Alpine.data('locateMe', () => ({
    locating: false,
    failed: false,

    locate() {
        if (!('geolocation' in navigator)) {
            this.failed = true;
            return;
        }

        this.locating = true;
        this.failed = false;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const form = this.$el.closest('form');
                form.querySelector('[name=lat]').value = position.coords.latitude.toFixed(5);
                form.querySelector('[name=lng]').value = position.coords.longitude.toFixed(5);
                form.querySelector('[name=near_me]').value = '1';
                form.submit();
            },
            () => {
                this.locating = false;
                this.failed = true;
            },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 },
        );
    },
}));

/* Editable list of vaccinations (name + date) on the dog form. Initial rows come from data-items. */
Alpine.data('vaccinationList', () => ({
    items: [],

    init() {
        const initial = JSON.parse(this.$el.dataset.items || '[]');
        this.items = initial.map((item) => ({ name: item.name || '', date: item.date || '' }));
    },

    add() {
        this.items.push({ name: '', date: '' });
        this.$nextTick(() => {
            const inputs = this.$el.querySelectorAll('[data-vaccine-name]');
            inputs[inputs.length - 1]?.focus();
        });
    },

    remove(index) {
        this.items.splice(index, 1);
    },

    fieldName(index, key) {
        return `vaccinations[${index}][${key}]`;
    },

    fieldId(index, key) {
        return `vaccination-${index}-${key}`;
    },
}));

/* Asks for confirmation before submitting a destructive form (message in data-confirm). */
Alpine.data('confirmSubmit', () => ({
    submit(event) {
        if (!window.confirm(this.$el.dataset.confirm)) {
            event.preventDefault();
        }
    },
}));

/* <details> dropdown (language, account menu): closes on Escape (focus back to summary) and on outside click. */
Alpine.data('dropdown', () => ({
    close(event) {
        if (!this.$el.hasAttribute('open')) {
            return;
        }

        this.$el.removeAttribute('open');

        if (event && event.type === 'keydown') {
            this.$el.querySelector('summary').focus();
        }
    },
}));

Alpine.start();
