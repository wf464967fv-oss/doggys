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

/*
 * Postal code / locality suggestions for a search input (native <datalist>, filled from /api/v1/places).
 * Typing a place also clears coordinates left by a previous "around me" search.
 */
Alpine.data('placeSuggest', () => ({
    timer: null,

    suggest() {
        const input = this.$el;
        const form = input.closest('form');
        form?.querySelectorAll('[name=lat], [name=lng], [name=near_me]').forEach((field) => { field.value = ''; });

        clearTimeout(this.timer);
        const query = input.value.trim();
        if (query.length < 2) {
            return;
        }

        this.timer = setTimeout(async () => {
            try {
                const url = `${input.dataset.endpoint}?q=${encodeURIComponent(query)}&lang=${document.documentElement.lang}`;
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) {
                    return;
                }
                const { data } = await response.json();
                const list = document.getElementById(input.getAttribute('list'));
                list.replaceChildren(...data.map((place) => Object.assign(document.createElement('option'), { value: place })));
            } catch {
                /* Suggestions are a convenience: the search still works without them. */
            }
        }, 250);
    },
}));

/*
 * Leaflet maps ([data-map], see resources/js/map.js): Leaflet and the tiles are only downloaded when a map
 * comes close to the viewport, so a map below the fold never delays the page. Hidden maps (display: none)
 * never intersect and are never mounted.
 */
const maps = document.querySelectorAll('[data-map]');
if (maps.length > 0) {
    const mountMap = (element) => import('./map.js').then(({ mount }) => mount(element));
    const observer = new IntersectionObserver((entries) => {
        entries.filter((entry) => entry.isIntersecting).forEach((entry) => {
            observer.unobserve(entry.target);
            mountMap(entry.target);
        });
    }, { rootMargin: '200px' });
    /* Maps flagged data-map-click (provider page) load nothing until the visitor asks for them. */
    maps.forEach((element) => {
        if (element.hasAttribute('data-map-click')) {
            element.querySelector('[data-map-load]')?.addEventListener('click', () => mountMap(element));
        } else {
            observer.observe(element);
        }
    });
}

/* Search form folded into a summary on phones: always unfolded from 768px (CSS covers recent browsers). */
document.querySelectorAll('[data-search-form]').forEach((details) => {
    if (window.matchMedia('(min-width: 768px)').matches) {
        details.open = true;
    }
});

/*
 * Guide table of contents: folded on phones (the article starts on the first screen), always open from 1024px.
 * On desktop the summary cannot fold it back (mouse or keyboard).
 */
const desktop = window.matchMedia('(min-width: 1024px)');
document.querySelectorAll('[data-toc]').forEach((details) => {
    details.open = desktop.matches;
    details.querySelector('summary').addEventListener('click', (event) => {
        if (desktop.matches) {
            event.preventDefault();
        }
    });
});

/* Guides index: theme buttons filter the cards (all visible without JS); the URL hash keeps the choice. */
const guideGrid = document.querySelector('[data-guide-grid]');
if (guideGrid) {
    const buttons = document.querySelectorAll('[data-guide-filter]');
    const themes = [...buttons].map((button) => button.dataset.guideFilter);
    const apply = (theme) => {
        guideGrid.querySelectorAll('[data-guide-category]').forEach((card) => {
            card.hidden = theme !== '' && card.dataset.guideCategory !== theme;
        });
        buttons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.guideFilter === theme)));
    };
    buttons.forEach((button) => button.addEventListener('click', () => {
        const theme = button.dataset.guideFilter;
        history.replaceState(null, '', theme ? `#${theme}` : window.location.pathname);
        apply(theme);
    }));
    const initial = window.location.hash.slice(1);
    apply(themes.includes(initial) ? initial : '');
}
Alpine.start();

/* Service worker (offline page + asset cache): production only, flagged by data-sw on <html>. */
if ('serviceWorker' in navigator && document.documentElement.hasAttribute('data-sw')) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
