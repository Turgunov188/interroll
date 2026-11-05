/**
 * Catalog: filters, search and sorting over the server-rendered cards, plus the request cart
 * (several products in one request). Card data: ProductCard::forScript().
 *
 * Section tab (?cat=) → part type inside it (?type=) → machine make (?machine=). Spare parts for
 * stenters are found by machine first: a Monforts clip does not fit a Bruckner.
 */
const HARDNESS = { soft: [0, 79], mid: [80, 90], hard: [91, 100] };
const ELASTOMERS = ['pu', 'rz', 'si'];
const ALL = 'all';

const defaults = () => ({
    query: '',
    materials: { pu: true, rz: true, si: true },
    hardness: ALL,
    industries: [],
    machine: ALL,
});

/** ?cat= may also name a part type: open its section with that type picked. */
const fromAddress = (search, types) => {
    const params = new URLSearchParams(search);
    let category = params.get('cat') || ALL;
    let type = params.get('type') || ALL;

    const parent = Object.keys(types).find((section) => types[section].some((t) => t.value === category));
    if (parent) [category, type] = [parent, category];

    return { category, type, machine: params.get('machine') || ALL };
};

export default ({ products, types = {} }) => ({
    products,
    types,
    category: ALL,
    type: ALL,
    sort: 'popular',
    filtersOpen: false,
    cart: {},
    ...defaults(),

    init() {
        this.byCode = Object.fromEntries(this.products.map((p) => [p.code, p]));
        Object.assign(this, fromAddress(window.location.search, this.types));

        this.$watch('category', () => {
            this.type = ALL;
            if (!this.machines.includes(this.machine)) this.machine = ALL;
            this.remember();
            this.$nextTick(() => this.revealTabs());
        });
        this.$watch('type', () => this.remember());
        this.$nextTick(() => this.revealTabs());
        this.$watch('machine', () => this.remember());
    },

    // ---- filtering

    inSection(product, category = this.category) {
        return category === ALL || product.category === category;
    },

    /** Everything except the section and type (the tabs show counts per section). */
    matches(product) {
        const q = this.query.trim().toLowerCase();
        const range = HARDNESS[this.hardness];
        const hasHardness = product.hardness[1] > 0;

        if (product.material in this.materials && !this.materials[product.material]) return false;
        if (range && hasHardness && !(product.hardness[1] >= range[0] && product.hardness[0] <= range[1])) return false;
        if (this.industries.length && !product.industries.some((slug) => this.industries.includes(slug))) return false;
        if (this.machine !== ALL && product.machine !== this.machine) return false;

        return !q || product.text.includes(q);
    },

    get shown() {
        return this.products.filter((p) => this.matches(p) && this.inSection(p) && (this.type === ALL || p.type === this.type));
    },

    get section() {
        return this.products.filter((p) => this.inSection(p));
    },

    /** Machine makes present in the open section (the «Станок» list shows only these). */
    get machines() {
        return [...new Set(this.section.map((p) => p.machine).filter(Boolean))];
    },

    /** Material and hardness filters only mean something for cast parts. */
    get hasElastomers() {
        return this.section.some((p) => ELASTOMERS.includes(p.material));
    },

    get hasHardness() {
        return this.section.some((p) => p.hardness[1] > 0);
    },

    countType(type) {
        return this.products.filter((p) => this.matches(p) && this.inSection(p) && (type === ALL || p.type === type)).length;
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
        return this.products.filter((p) => this.matches(p) && this.inSection(p, category)).length;
    },

    reset() {
        Object.assign(this, defaults(), { category: ALL, type: ALL });
    },

    /** Phones: scroll the tab rows so the picked section and type are in view (rows are wider than the screen). */
    revealTabs() {
        this.$root.querySelectorAll('.catalog-tabs .segmented--scroll').forEach((row) => {
            const active = row.offsetParent && row.querySelector('[aria-pressed="true"]');
            if (active) row.scrollLeft = active.offsetLeft - row.offsetLeft - (row.clientWidth - active.offsetWidth) / 2;
        });
    },

    /** Keep ?cat=, ?type= and ?machine= in the address so the filtered page can be shared. */
    remember() {
        const url = new URL(window.location.href);
        for (const [key, value] of [['cat', this.category], ['type', this.type], ['machine', this.machine]]) {
            if (value === ALL) url.searchParams.delete(key);
            else url.searchParams.set(key, value);
        }
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
