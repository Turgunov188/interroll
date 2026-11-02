import { prism } from './prism.js';
import { dimH, dimV, rect, round, sheet } from './svg.js';

/** Plate / scraper / sheet: L length, W width, T thickness. */
export default {
    motion: 'turntable',
    pitch: -32, // flat parts are seen from above

    fix: (p) => p,

    chips: (p) => [['L', p.L], ['W', p.W], ['T', `${p.T} mm`], ['S', `${round((p.L * p.W) / 1e6)} m²`]],

    draw(p, o) {
        const k = Math.min(520 / p.L, 170 / p.W);
        const w = p.L * k;
        const h = p.W * k;
        const x0 = 320 - w / 2;
        const y0 = 70;
        const t = Math.max(6, p.T * k);
        const ty = y0 + h + 60;

        return sheet(
            rect(x0, y0, w, h, '#fff') + dimH(x0, x0 + w, y0 - 14, `L=${p.L}`) + dimV(x0 - 18, y0, y0 + h, `W=${p.W}`)
            + rect(x0, ty, w, t, 'url(#h)') + dimV(x0 + w + 24, ty, ty + t, `T=${p.T}`),
            o,
        );
    },

    faces(p, color) {
        const k = 300 / Math.max(p.L, p.W);
        const w = p.L * k;
        const h = Math.max(24, p.W * k);
        const t = Math.max(5, p.T * k);
        const outline = [[-w / 2, -h / 2], [w / 2, -h / 2], [w / 2, h / 2], [-w / 2, h / 2]];

        // laid flat, so the turntable never shows it edge-on
        return { faces: prism(outline, t, color, color).map((css) => css.replace('transform:', 'transform:rotateX(90deg) ')) };
    },
};
