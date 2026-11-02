import { HUB, HOLE, ngon, prism } from './prism.js';
import { axis, circle, dimH, dimV, rect, round, sheet, text } from './svg.js';

/** Wheel / tyre on a disc / guide roller: D outer, d bore, B width, T polyurethane thickness. */
const SIZE = 230;
const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));

export default {
    motion: 'turntable',
    pitch: -24,

    fix(p) {
        p.d = clamp(p.d, 5, p.D - 20);
        p.T = clamp(p.T, 2, (p.D - p.d) / 2 - 3);
        return p;
    },

    chips: (p) => [['D', `Ø${p.D}`], ['d', `Ø${p.d}`], ['B', p.B], ['T', `${p.T} mm`]],

    draw(p, o) {
        const k = Math.min(240 / p.D, 220 / p.B);
        const R = (p.D / 2) * k;
        const cx = 170;
        const cy = 190;
        const hub = (p.D / 2 - p.T) * k;
        const bore = (p.d / 2) * k;
        const w = p.B * k;
        const x0 = 450 - w / 2;

        return sheet(
            axis(cx - R - 20, cy, cx + R + 20, cy) + axis(cx, cy - R - 20, cx, cy + R + 20)
            + circle(cx, cy, R, 'url(#h)') + circle(cx, cy, hub, '#d2d7dc') + circle(cx, cy, bore, '#fff')
            + dimH(cx - R, cx + R, cy - R - 18, `Ø${p.D}`) + text(cx, cy + 5, `Ø${p.d}`, { size: 12 })
            + rect(x0, cy - R, w, R - hub, 'url(#h)') + rect(x0, cy + hub, w, R - hub, 'url(#h)')
            + rect(x0, cy - hub, w, hub - bore, '#d2d7dc') + rect(x0, cy + bore, w, hub - bore, '#d2d7dc')
            + axis(x0 - 20, cy, x0 + w + 20, cy) + dimH(x0, x0 + w, cy + R + 28, `B=${p.B}`) + dimV(x0 + w + 26, cy - R, cy - hub, `T=${p.T}`),
            o,
        );
    },

    faces(p, color) {
        const k = SIZE / Math.max(p.D, p.B);
        const R = (p.D / 2) * k;
        const hub = (p.D / 2 - p.T) * k;
        const bore = Math.max(2, (p.d / 2) * k);
        const B = Math.max(6, p.B * k);
        const cap = `radial-gradient(circle closest-side,${HOLE} 0 ${round((bore / R) * 100)}%,${HUB} ${round((bore / R) * 100 + 0.6)}% ${round((hub / R) * 100)}%,${color} ${round((hub / R) * 100 + 0.6)}% 100%)`;

        return { faces: prism(ngon(R, 36), B, color, cap, 'circle').concat(prism(ngon(bore, 18), B + 0.5, color, null, null, true)) };
    },
};
