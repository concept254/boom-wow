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

Alpine.start();
