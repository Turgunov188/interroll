import { accentColor } from '../core/env.js';
import { renderFaces } from '../effects/roller-model.js';
import { SHAPES, shapeValues } from '../shapes/index.js';

/**
 * Product page: view switcher (drawing / 3D / photos), the configurator (material, hardness,
 * sizes) with a live drawing and 3D model, and the request summary sent with the form.
 * Config: resources/views/site/pages/product.blade.php ($config).
 */
const COATING = { nbr: '#2a3038', epdm: '#4a535e' };

export default (config) => ({
    view: config.view,
    tab: config.tab,
    material: config.material,
    shore: config.hardness,
    qty: 4,
    picked: {},
    pointer: { x: 0, y: 0 },
    parameters: config.parameters,

    init() {
        this.shape = SHAPES[config.type] ?? null;
        if (!this.shape) return;

        this.$watch('view', () => this.render3d());
        this.$watch('values', () => this.render3d());
        this.$watch('material', () => this.render3d());
        this.render3d();
    },

    // ---- configuration

    get values() {
        return this.shape ? shapeValues(config.type, this.parameters, this.picked) : {};
    },

    set(key, value) {
        this.picked = { ...this.picked, [key]: Number(value) };
    },

    /** Slider maximum; the roller coating can't be thicker than the diameter allows. */
    limit(param) {
        return this.shape?.limits?.(this.values)[param.key] ?? param.max;
    },

    get color() {
        return COATING[this.material] ?? accentColor();
    },

    get materialName() {
        return config.texts.long[this.material] ?? '';
    },

    get hardnessPosition() {
        return (((this.shore - 60) / 38) * 100).toFixed(1);
    },

    get hardnessHint() {
        const hints = config.texts.hints;
        return this.shore < 80 ? hints.soft : this.shore < 92 ? hints.mid : hints.hard;
    },

    get sizes() {
        return this.parameters.map((p) => `${p.key} ${this.values[p.key]}`).join(' × ') + ' mm';
    },

    get chips() {
        return this.shape ? this.shape.chips(this.values).concat(config.type === 'roller' ? [['Shore', `${this.shore} A`]] : []) : [];
    },

    get scale() {
        return this.shape?.scale?.(this.values) ?? '';
    },

    // ---- drawing

    get drawing() {
        if (!this.shape) return '';
        const t = config.texts;
        const note = `${t.short[this.material]} · ${this.shore} Sh A`;

        return this.shape.draw(this.values, {
            stamp: t.stamp,
            note: config.type === 'roller' ? `${note} · ${t.coating} ${this.values.T} mm` : note,
            label: t.drawing.replace(':sizes', this.sizes),
            color: this.color,
            opacity: this.material === 'pu' ? (0.45 + ((this.shore - 60) / 38) * 0.55).toFixed(2) : 0.9,
        });
    },

    // ---- 3D

    get motion() {
        return this.shape?.motion ?? 'turntable';
    },

    render3d() {
        if (!this.shape || this.view !== '3d' || !this.$refs.model) return;
        const { faces, length = 0 } = this.shape.faces(this.values, this.color);
        this.modelLength = length;
        renderFaces(this.$refs.model, faces, this.motion === 'roll' ? 'stage3d__face' : 'stage3d__panel');
    },

    tilt(event) {
        if (!event) {
            this.pointer = { x: 0, y: 0 };
            return;
        }
        const box = event.currentTarget.getBoundingClientRect();
        this.pointer = { x: (event.clientX - box.left) / box.width - 0.5, y: (event.clientY - box.top) / box.height - 0.5 };
    },

    get tiltStyle() {
        const { x, y } = this.pointer;
        if (this.motion === 'roll') {
            const half = (this.modelLength || 0) / 2;
            return `left:${-half}px;width:${2 * half}px;transform-origin:${half}px 0;transform:rotateY(${-24 + x * 70}deg) rotateX(${14 - y * 40}deg)`;
        }
        return `transform:rotateX(${(this.shape?.pitch ?? -24) + y * 40}deg) rotateY(${x * 70}deg)`;
    },

    get modelStyle() {
        const half = (this.modelLength || 0) / 2;
        return this.motion === 'roll'
            ? `position:absolute;left:0;top:0;width:${2 * half}px;height:0;transform-origin:${half}px 0`
            : 'position:absolute;left:0;top:0;width:0;height:0';
    },

    loadModelViewer() {
        import('@google/model-viewer');
    },

    // ---- request

    get summary() {
        const head = `${config.code} ${config.name}`;
        if (!this.shape) return `${head} · ${this.qty} ${config.texts.pcs}`;

        return [head, this.materialName, `${this.shore} Shore A`, this.sizes, `${this.qty} ${config.texts.pcs}`].join(' · ');
    },

    get itemsJson() {
        const options = this.shape ? { mat: this.material, shore: this.shore, ...this.values } : {};
        return JSON.stringify([{ code: config.code, qty: this.qty, options }]);
    },
});
