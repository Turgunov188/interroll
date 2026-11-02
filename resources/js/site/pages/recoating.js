import { formatNumber, prefersReducedMotion } from '../core/env.js';

/**
 * Recoating page: the process scheme plays its four stages (a click pins one), the savings
 * calculator, the coating material choice (it goes into the request) and the request item.
 */
const STAGE_MS = 2600;

export default ({ materials, texts }) => ({
    stage: 0,
    hold: false,
    material: 'pu',
    price: 5000000,
    quantity: 6,
    ratio: 50,
    sizes: { D: '', L: '', qty: '' },

    init() {
        if (prefersReducedMotion()) return;
        this.timer = setInterval(() => {
            if (!this.hold && !document.hidden) this.stage = (this.stage + 1) % 4;
        }, STAGE_MS);
    },

    destroy() {
        clearInterval(this.timer);
    },

    pick(index) {
        this.stage = index;
        this.hold = true;
    },

    // ---- process scheme

    get materialName() {
        return materials[this.material]?.name ?? '';
    },

    get materialColor() {
        return materials[this.material]?.color ?? '#f5b700';
    },

    /** Coating rectangle: worn (thin, grey), stripped (gone), new (full, material colour). */
    get coat() {
        const worn = this.stage === 0;
        const stripped = this.stage === 1;

        return {
            y: worn ? 96 : stripped ? 112 : 82,
            height: worn ? 108 : stripped ? 76 : 136,
            fill: worn ? '#8a939d' : this.materialColor,
            opacity: stripped ? 0 : worn ? 0.85 : 0.9,
        };
    },

    get coatCaption() {
        if (this.stage === 0) return texts.worn;
        if (this.stage === 1) return texts.stripped;
        return texts.newLayer.replace(':material', this.materialName.toUpperCase());
    },

    get stageLabel() {
        return texts.label.replace(':stage', texts.stages[this.stage]);
    },

    // ---- calculator

    get newTotal() {
        return Math.max(0, Number(this.price) || 0) * this.quantity;
    },

    get recoatTotal() {
        return (this.newTotal * this.ratio) / 100;
    },

    format: formatNumber,

    // ---- request

    get itemsJson() {
        const options = { mat: this.material };
        if (this.sizes.D) options.D = this.sizes.D;
        if (this.sizes.L) options.L = this.sizes.L;
        const qty = Number.parseInt(String(this.sizes.qty).replace(/\D/g, ''), 10) || 1;

        return JSON.stringify([{ code: 'IR-K01', qty, options }]);
    },
});
