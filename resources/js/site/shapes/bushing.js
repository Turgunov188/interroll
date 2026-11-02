import { ngon, prism, ringCap } from './prism.js';
import { axis, circle, dimH, rect, round, sheet, text } from './svg.js';

/** Bushing / die spring / sleeve: D outer, d bore, L length. */
const SIZE = 230;

export default {
    motion: 'turntable',
    pitch: -24,

    fix(p) {
        p.d = Math.max(2, Math.min(p.d, p.D - 4));
        return p;
    },

    chips: (p) => [['D', `Ø${p.D}`], ['d', `Ø${p.d}`], ['L', p.L], ['S', `${round((p.D - p.d) / 2)} mm`]],

    draw(p, o) {
        const k = Math.min(220 / p.D, 250 / p.L);
        const R = (p.D / 2) * k;
        const r = (p.d / 2) * k;
        const cx = 160;
        const cy = 190;
        const w = p.L * k;
        const x0 = 440 - w / 2;

        return sheet(
            axis(cx - R - 20, cy, cx + R + 20, cy) + axis(cx, cy - R - 20, cx, cy + R + 20)
            + circle(cx, cy, R, 'url(#h)') + circle(cx, cy, r, '#fff')
            + dimH(cx - R, cx + R, cy - R - 18, `Ø${p.D}`) + text(cx, cy + 5, `Ø${p.d}`, { size: 12 })
            + rect(x0, cy - R, w, R - r, 'url(#h)') + rect(x0, cy + r, w, R - r, 'url(#h)')
            + axis(x0 - 20, cy, x0 + w + 20, cy) + dimH(x0, x0 + w, cy + R + 28, `L=${p.L}`),
            o,
        );
    },

    faces(p, color) {
        const k = SIZE / Math.max(p.D, p.L);
        const R = (p.D / 2) * k;
        const r = Math.max(2, (p.d / 2) * k);
        const L = Math.max(6, p.L * k);

        return { faces: prism(ngon(R, 32), L, color, ringCap(r, R, color), 'circle').concat(prism(ngon(r, 18), L + 0.5, color, null, null, true)) };
    },
};
