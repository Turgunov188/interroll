/**
 * CSS 3D prisms: a polygon extruded by a depth — side faces along the edges plus two caps.
 * Every part of the configurator (except the roller) is built from these.
 * Functions return the CSS text of each face (see renderFaces()).
 */
import { round } from './svg.js';

export const HOLE = '#1e2227';
export const HUB = '#8a939d';

/** Faces lit from the top-left get lighter. */
const shade = (color, nx, ny) => {
    const light = 0.5 + 0.5 * (-0.6 * nx - 0.8 * ny);
    const o = (0.32 * (1 - light)).toFixed(3);
    return `linear-gradient(rgba(17,20,24,${o}),rgba(17,20,24,${o})),${color}`;
};

const face = (w, h, transform, background, extra = '') =>
    `left:${round(-w / 2)}px;top:${round(-h / 2)}px;width:${round(w)}px;height:${round(h)}px;transform:${transform};background:${background};${extra}`;

/**
 * @param {Array<[number, number]>} points  polygon, px
 * @param {number} depth                    px
 * @param {string} color                    side colour
 * @param {string|null} capBackground       caps (null: open tube, e.g. a bore)
 * @param {'circle'|'polygon'} capShape
 * @param {boolean} inward                  faces of a hole (dark)
 */
export function prism(points, depth, color, capBackground = null, capShape = 'polygon', inward = false) {
    const faces = [];

    points.forEach((a, i) => {
        const b = points[(i + 1) % points.length];
        const dx = b[0] - a[0];
        const dy = b[1] - a[1];
        const length = Math.hypot(dx, dy);
        if (length < 0.01) return;

        const angle = (Math.atan2(dy, dx) * 180) / Math.PI;
        const nx = dy / length;
        const ny = -dx / length;
        const background = inward ? `linear-gradient(rgba(0,0,0,.35),rgba(0,0,0,.35)),${HOLE}` : shade(color, nx, ny);

        faces.push(face(length + 0.6, depth,
            `translate3d(${round((a[0] + b[0]) / 2)}px,${round((a[1] + b[1]) / 2)}px,0) rotateZ(${round(angle)}deg) rotateX(90deg)`, background));
    });

    if (capBackground) {
        const xs = points.map((p) => p[0]);
        const ys = points.map((p) => p[1]);
        const [minX, maxX, minY, maxY] = [Math.min(...xs), Math.max(...xs), Math.min(...ys), Math.max(...ys)];
        const w = maxX - minX;
        const h = maxY - minY;
        const clip = capShape === 'circle'
            ? 'border-radius:50%;'
            : `clip-path:polygon(${points.map((p) => `${round(((p[0] - minX) / w) * 100)}% ${round(((p[1] - minY) / h) * 100)}%`).join(',')});`;

        for (const z of [depth / 2, -depth / 2]) {
            faces.push(face(w, h, `translate3d(${round((minX + maxX) / 2)}px,${round((minY + maxY) / 2)}px,${round(z)}px)`, capBackground, clip));
        }
    }

    return faces;
}

/** Regular polygon around the origin (circles are 32–36-gons). */
export const ngon = (r, n) => Array.from({ length: n }, (_, i) => {
    const a = (i / n) * Math.PI * 2;
    return [r * Math.cos(a), r * Math.sin(a)];
});

/** Cap of a tube: dark hole, then the material. */
export const ringCap = (inner, outer, color) => {
    const edge = round((inner / outer) * 100);
    return `radial-gradient(circle closest-side,${HOLE} 0 ${edge}%,${color} ${round(edge + 0.6)}% 100%)`;
};

/** Outline of a coupling spider with z jaws. */
export function starPoints(radius, jaws) {
    const inner = radius * 0.64;
    const step = (Math.PI * 2) / jaws;
    const outerHalf = step * 0.22;
    const innerHalf = step * 0.18;
    const points = [];

    for (let i = 0; i < jaws; i++) {
        const a = i * step - Math.PI / 2;
        points.push([radius * Math.cos(a - outerHalf), radius * Math.sin(a - outerHalf)], [radius * Math.cos(a + outerHalf), radius * Math.sin(a + outerHalf)]);
        points.push([inner * Math.cos(a + step / 2 - innerHalf), inner * Math.sin(a + step / 2 - innerHalf)], [inner * Math.cos(a + step / 2 + innerHalf), inner * Math.sin(a + step / 2 + innerHalf)]);
    }

    return points;
}
