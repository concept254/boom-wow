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

Alpine.start();
