import { rollerFaces } from '../effects/roller-model.js';
import { axis, line, rect, round, sheet, text } from './svg.js';

/** Roller / shaft coating: D outer diameter, L working length, T coating thickness. */
const fit = ({ D, L }) => Math.min(420 / L, 190 / D);

export default {
    motion: 'roll',

    fix(p) {
        p.T = Math.min(p.T, Math.max(6, Math.min(40, Math.floor(p.D / 2 - 12))));
        return p;
    },

    /** Upper limit of the coating slider for the current diameter. */
    limits: (p) => ({ T: Math.max(6, Math.min(40, Math.floor(p.D / 2 - 12))) }),

    chips: (p) => [['D', `Ø${p.D}`], ['L', p.L], ['T', `${p.T} mm`]],

    scale: (p) => round(1 / fit(p)),

    draw(p, o) {
        const k = fit(p);
        const w = p.L * k;
        const h = p.D * k;
        const cx = 320;
        const cy = 170;
        const x = cx - w / 2;
        const y = cy - h / 2;
        const core = Math.max(4, (p.D - 2 * p.T) * k);
        const shaft = Math.max(6, core * 0.42);

        return sheet(
            axis(40, cy, 600, cy)
            + rect(x - 40, cy - shaft / 2, w + 80, shaft, '#4a535e', { width: 1.5 })
            + rect(x, y, w, h, o.color, { width: 2, opacity: o.opacity })
            + rect(x, cy - core / 2, w, core, 'url(#core)', { width: 1.5 })
            + line(x, y + h + 34, x + w, y + h + 34) + line(x, y + h + 24, x, y + h + 44) + line(x + w, y + h + 24, x + w, y + h + 44)
            + line(x + w + 64, y, x + w + 64, y + h) + line(x + w + 54, y, x + w + 74, y) + line(x + w + 54, y + h, x + w + 74, y + h)
            + text(cx, y + h + 60, `L = ${p.L}`)
            + text(x + w + 80, cy + 5, `Ø${p.D}`, { anchor: 'start' })
            + text(x, y - 16, o.note, { anchor: 'start' }),
            { stamp: o.stamp, label: o.label },
        );
    },

    faces(p, color) {
        const k = fit(p);
        const w = p.L * k;
        const h = p.D * k;
        const core = Math.max(4, (p.D - 2 * p.T) * k);
        const length = Math.max(60, Math.min(520, w * 1.05));
        const radius = Math.max(26, Math.min(130, h * 0.62));
        const coreRatio = (core / h) * 50;

        return {
            length,
            faces: rollerFaces({
                length,
                radius,
                shaftRadius: Math.max(6, radius * (core / h) * 0.42),
                shaftExtra: 120,
                color,
                cap: { hub: coreRatio * 0.42, ring: coreRatio, ringColor: '#8a939d' },
            }),
        };
    },
};
