/**
 * Catalog: filters, search and sorting over the server-rendered cards, plus the request cart
 * (several products in one request). Card data: ProductCard::forScript().
 */
const HARDNESS = { soft: [0, 79], mid: [80, 90], hard: [91, 100] };

const defaults = () => ({
    query: '',
    materials: { pu: true, rz: true },
    hardness: 'all',
    industries: [],
});

export default ({ products, category = 'all' }) => ({
    products,
    category,
    sort: 'popular',
    filtersOpen: false,
    cart: {},
    ...defaults(),

    init() {
        this.byCode = Object.fromEntries(this.products.map((p) => [p.code, p]));
        this.$watch('category', (value) => this.rememberCategory(value));
    },

    // ---- filtering

    /** Matches everything except the category (the tabs show counts per category). */
    matches(product) {
        const q = this.query.trim().toLowerCase();
        const range = HARDNESS[this.hardness];

        if (product.material in this.materials && !this.materials[product.material]) return false;
        if (range && !(product.hardness[1] >= range[0] && product.hardness[0] <= range[1])) return false;
        if (this.industries.length && !product.industries.some((slug) => this.industries.includes(slug))) return false;

        return !q || product.text.includes(q);
    },

    get shown() {
        return this.products.filter((p) => this.matches(p) && (this.category === 'all' || p.category === this.category));
    },

    get ranking() {
        const list = [...this.shown];
        if (this.sort === 'code') list.sort((a, b) => a.code.localeCompare(b.code));
        if (this.sort === 'name') list.sort((a, b) => a.name.localeCompare(b.name));

        return Object.fromEntries(list.map((p, i) => [p.code, i]));
    },

    isShown(code) {
        return code in this.ranking;
    },

    position(code) {
        return this.ranking[code] ?? 0;
    },

    countIn(category) {
        return this.products.filter((p) => this.matches(p) && (category === 'all' || p.category === category)).length;
    },

    reset() {
        Object.assign(this, defaults(), { category: 'all' });
    },

    /** Keep ?cat= in the address so the filtered page can be shared. */
    rememberCategory(value) {
        const url = new URL(window.location.href);
        if (value === 'all') url.searchParams.delete('cat');
        else url.searchParams.set('cat', value);
        window.history.replaceState(null, '', url);
    },

    // ---- request cart

    inCart(code) {
        return code in this.cart;
    },

    toggle(code) {
        const next = { ...this.cart };
        if (next[code]) delete next[code];
        else next[code] = 1;
        this.cart = next;
    },

    change(code, delta) {
        const qty = (this.cart[code] || 0) + delta;
        const next = { ...this.cart };
        if (qty <= 0) delete next[code];
        else next[code] = qty;
        this.cart = next;
    },

    get cartSize() {
        return Object.keys(this.cart).length;
    },

    get cartLines() {
        return Object.entries(this.cart).map(([code, qty]) => ({ code, qty, name: this.byCode[code]?.name ?? code, icon: this.byCode[code]?.icon ?? '' }));
    },

    get itemsJson() {
        return JSON.stringify(Object.entries(this.cart).map(([code, qty]) => ({ code, qty })));
    },

    summary(template) {
        const pieces = Object.values(this.cart).reduce((sum, qty) => sum + qty, 0);
        return template.replace(':parts', this.cartSize).replace(':pieces', pieces);
    },

    openCart() {
        this.$refs.cart.showModal();
    },

    closeCart() {
        this.$refs.cart.close();
        this.$refs.cart.querySelector('[x-data]')?.dispatchEvent(new CustomEvent('lead:reset'));
    },

    clearCart() {
        this.cart = {};
    },
});
