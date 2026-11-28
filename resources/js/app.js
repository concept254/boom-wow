import Alpine from 'alpinejs';

window.Alpine = Alpine;

/** Booking wizard: business -> service -> staff -> date -> time slot. */
Alpine.data('bookingForm', (businesses, slotsUrl, preset = {}) => ({
    businesses,
    businessId: preset.businessId ?? '',
    serviceId: preset.serviceId ?? '',
    staffId: '',
    date: '',
    time: '',
    slots: [],
    loading: false,
    message: '',
    get services() {
        return (this.businesses.find((b) => String(b.id) === String(this.businessId)) || { services: [] }).services;
    },
    get service() {
        return this.services.find((s) => String(s.id) === String(this.serviceId));
    },
    get staff() {
        return this.service ? this.service.staff : [];
    },
    resetFrom(level) {
        if (level <= 1) { this.serviceId = ''; }
        if (level <= 2) { this.staffId = ''; }
        this.date = ''; this.time = ''; this.slots = []; this.message = '';
    },
    async loadSlots() {
        this.time = ''; this.slots = []; this.message = '';
        if (!this.serviceId || !this.date) { return; }
        this.loading = true;
        try {
            const params = new URLSearchParams({ service: this.serviceId, date: this.date });
            if (this.staffId) { params.set('staff', this.staffId); }
            const res = await fetch(`${slotsUrl}?${params}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            this.slots = data.slots || [];
            if (!this.slots.length) { this.message = 'No times available on this date. Please try another day.'; }
        } catch (e) {
            this.message = 'Could not load times. Please try again.';
        } finally {
            this.loading = false;
        }
    },
}));

/** Hero slideshow: autoplay (off for reduced-motion users or a single slide), pause on hover/focus. */
Alpine.data('heroSlider', (count, interval = 6500) => ({
    i: 0,
    paused: false,
    init() {
        if (count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
        setInterval(() => { if (!this.paused && !document.hidden) { this.next(); } }, interval);
    },
    next() { this.i = (this.i + 1) % count; },
    prev() { this.i = (this.i - 1 + count) % count; },
    go(n) { this.i = n; },
}));

/** Hero background: only swap in the image once it has loaded, so a dead remote URL never shows a broken box. */
Alpine.data('heroImage', (url) => ({
    loaded: false,
    init() {
        const img = new Image();
        img.onload = () => { this.loaded = true; };
        img.src = url;
    },
}));

/** Dashboard charts (Chart.js is loaded on demand, only on pages that have a chart). */
async function initCharts() {
    const nodes = document.querySelectorAll('canvas[data-chart]');
    if (!nodes.length) { return; }
    const { default: Chart } = await import('chart.js/auto');
    const palette = ['#F45422', '#201E1F', '#393536', '#D94316', '#8a8586'];
    nodes.forEach((el) => {
        const cfg = JSON.parse(el.dataset.chart);
        new Chart(el, {
            type: cfg.type || 'bar',
            data: {
                labels: cfg.labels,
                datasets: [{ label: cfg.label || '', data: cfg.data, backgroundColor: cfg.type === 'doughnut' ? palette : palette[0], borderWidth: 0, borderRadius: 4 }],
            },
            options: { responsive: true, plugins: { legend: { display: cfg.type === 'doughnut' } }, scales: cfg.type === 'doughnut' ? {} : { y: { beginAtZero: true } } },
        });
    });
}
document.addEventListener('DOMContentLoaded', initCharts);

/** Product / service photo gallery: thumbnails, previous/next, hover zoom, and a full-screen lightbox with keyboard support. */
Alpine.data('productGallery', (slides) => ({
    slides,
    i: 0,
    lightbox: false,
    zoom: false,
    origin: '50% 50%',
    get current() { return this.slides[this.i]; },
    go(n) { this.i = (n + this.slides.length) % this.slides.length; },
    next() { this.go(this.i + 1); },
    prev() { this.go(this.i - 1); },
    move(e) {
        const r = e.currentTarget.getBoundingClientRect();
        this.origin = `${((e.clientX - r.left) / r.width) * 100}% ${((e.clientY - r.top) / r.height) * 100}%`;
    },
    open() { if (!this.current.placeholder) { this.lightbox = true; document.body.classList.add('overflow-hidden'); } },
    close() { this.lightbox = false; document.body.classList.remove('overflow-hidden'); },
}));

/** Photo grid with a full-screen viewer (business galleries). */
Alpine.data('galleryGrid', (slides) => ({
    slides,
    i: 0,
    open: false,
    get current() { return this.slides[this.i]; },
    show(n) { this.i = n; this.open = true; document.body.classList.add('overflow-hidden'); },
    close() { this.open = false; document.body.classList.remove('overflow-hidden'); },
    go(n) { this.i = (n + this.slides.length) % this.slides.length; },
    next() { this.go(this.i + 1); },
    prev() { this.go(this.i - 1); },
}));

/** Quantity stepper for the buy box. */
Alpine.data('qtyStepper', (max = 99) => ({
    qty: 1,
    max,
    inc() { this.qty = Math.min(this.max, this.qty + 1); },
    dec() { this.qty = Math.max(1, this.qty - 1); },
    clamp() { this.qty = Math.min(this.max, Math.max(1, parseInt(this.qty, 10) || 1)); },
}));

/** Share buttons: native share sheet where available, plus copy-link feedback. */
Alpine.data('shareLinks', (url, title) => ({
    copied: false,
    canNative: typeof navigator !== 'undefined' && !!navigator.share,
    async copy() {
        try { await navigator.clipboard.writeText(url); this.copied = true; setTimeout(() => (this.copied = false), 2000); } catch (e) { window.prompt('Copy this link', url); }
    },
    native() { navigator.share({ title, url }).catch(() => {}); },
}));

/* ---- Loading states (styles in app.css) ---------------------------------- */
(function loadingStates() {
    const root = document.documentElement;

    function done(img) {
        img.classList.add('is-loaded');
        const p = img.parentElement;
        if (p) {
            p.classList.remove('bw-skel');
            p.querySelectorAll(':scope > .bw-spin').forEach((s) => s.remove());
        }
    }

    function track(img) {
        img.classList.remove('is-loaded');
        if (img.complete) { done(img); return; }          // cached, or failed: nothing to wait for
        const p = img.parentElement;
        if (p) {
            p.classList.add('bw-skel');
            if (img.hasAttribute('data-spinner')) {
                if (getComputedStyle(p).position === 'static') p.style.position = 'relative';
                if (!p.querySelector(':scope > .bw-spin')) {
                    const s = document.createElement('span');
                    s.className = 'bw-spin';
                    s.setAttribute('aria-hidden', 'true');
                    p.appendChild(s);
                }
            }
        }
        img.addEventListener('load', () => done(img), { once: true });
        img.addEventListener('error', () => done(img), { once: true });
    }

    const selector = 'img[loading="lazy"], img[data-spinner]';
    document.querySelectorAll(selector).forEach((img) => { if (img.complete) img.classList.add('is-loaded'); });
    root.classList.add('js');                              // only now can images be hidden, so there is no flash
    document.querySelectorAll(selector).forEach((img) => { if (!img.complete) track(img); });

    // Images whose src is swapped by Alpine (product gallery) show the spinner again for the next photo.
    new MutationObserver((list) => list.forEach((m) => {
        if (m.target.matches && m.target.matches('img[data-spinner]')) track(m.target);
    })).observe(document.body, { subtree: true, attributes: true, attributeFilter: ['src'] });

    // Thin progress bar while the next page loads.
    const bar = document.createElement('div');
    bar.id = 'bw-progress';
    bar.setAttribute('aria-hidden', 'true');
    document.body.appendChild(bar);
    let barTimer;
    const start = () => { bar.classList.remove('is-active'); void bar.offsetWidth; bar.classList.add('is-active'); clearTimeout(barTimer); barTimer = setTimeout(stop, 15000); };   // a download keeps the page open: never leave the bar hanging
    const stop = () => bar.classList.remove('is-active');

    document.addEventListener('click', (e) => {
        const a = e.target.closest && e.target.closest('a[href]');
        if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (a.target && a.target !== '_self') return;
        if (a.hasAttribute('download') || a.hasAttribute('data-no-progress')) return;
        const url = new URL(a.href, location.href);
        if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)) return;
        if (url.pathname === location.pathname && url.search === location.search) return;   // same page / #anchor
        start();
    });

    // Forms: progress bar plus a spinner inside the pressed button. Not disabled, so button values still submit.
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (e.defaultPrevented || form.hasAttribute('data-no-busy')) return;
        if (form.dataset.submitting) { e.preventDefault(); return; }    // ignore double clicks
        form.dataset.submitting = '1';
        const btn = e.submitter || form.querySelector('button[type="submit"], button:not([type])');
        if (btn && !btn.querySelector('.bw-spin-inline')) {
            btn.classList.add('is-busy');
            btn.setAttribute('aria-busy', 'true');
            const s = document.createElement('span');
            s.className = 'bw-spin-inline';
            s.setAttribute('aria-hidden', 'true');
            btn.prepend(s);
        }
        start();
        setTimeout(() => { delete form.dataset.submitting; if (btn) { btn.classList.remove('is-busy'); btn.removeAttribute('aria-busy'); btn.querySelectorAll('.bw-spin-inline').forEach((x) => x.remove()); } }, 20000);   // e.g. a file download: the page never changes
    });

    // Back/forward cache: undo the busy state when the page is shown again.
    window.addEventListener('pageshow', (e) => {
        stop();
        if (!e.persisted) return;
        document.querySelectorAll('form[data-submitting]').forEach((f) => delete f.dataset.submitting);
        document.querySelectorAll('.is-busy').forEach((b) => { b.classList.remove('is-busy'); b.removeAttribute('aria-busy'); b.querySelectorAll('.bw-spin-inline').forEach((s) => s.remove()); });
    });
})();

Alpine.start();
