import { prism, ringCap, starPoints } from './prism.js';
import { axis, circle, dimH, polygon, rect, sheet, text } from './svg.js';

/** Coupling spider: D outer, d bore, z jaws, B thickness. */
const SIZE = 230;

export default {
    motion: 'turntable',
    pitch: -24,

    fix(p) {
        p.d = Math.max(3, Math.min(p.d, p.D * 0.55));
        p.z = Math.round(Math.max(3, Math.min(12, p.z)));
        return p;
    },

    chips: (p) => [['D', `Ø${p.D}`], ['d', `Ø${p.d}`], ['z', p.z], ['B', `${p.B} mm`]],

    draw(p, o) {
        const k = 240 / p.D;
        const R = (p.D / 2) * k;
        const cx = 170;
        const cy = 190;
        const w = Math.max(6, p.B * k);
        const x0 = 450 - w / 2;

        return sheet(
            axis(cx - R - 20, cy, cx + R + 20, cy) + axis(cx, cy - R - 20, cx, cy + R + 20)
            + polygon(starPoints(R, p.z).map(([x, y]) => [x + cx, y + cy]), 'url(#h)') + circle(cx, cy, (p.d / 2) * k, '#fff')
            + dimH(cx - R, cx + R, cy - R - 18, `Ø${p.D}`) + text(cx, cy + 5, `Ø${p.d}`, { size: 12 })
            + rect(x0, cy - R, w, 2 * R, 'url(#h)') + dimH(x0, x0 + w, cy + R + 28, `B=${p.B}`) + text(x0 + w + 16, cy, `z=${p.z}`, { anchor: 'start' }),
            o,
        );
    },

    faces(p, color) {
        const k = SIZE / p.D;
        const R = (p.D / 2) * k;
        const B = Math.max(6, p.B * k);

        return { faces: prism(starPoints(R, p.z), B, color, ringCap(Math.max(2, (p.d / 2) * k), R, color), 'polygon') };
    },
};
