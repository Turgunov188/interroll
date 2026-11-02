/**
 * Parametric parts of the product configurator. Each shape draws its technical drawing (SVG)
 * and builds its CSS 3D model from the sizes the visitor picks. The sliders (keys, limits,
 * defaults) come from the server: App\Enums\Configurator::parameters().
 */
import bushing from './bushing.js';
import plate from './plate.js';
import roller from './roller.js';
import spider from './spider.js';
import wheel from './wheel.js';

export const SHAPES = { roller, wheel, bushing, plate, spider };

/** Current sizes: server defaults, then what the visitor picked, clamped and made consistent. */
export function shapeValues(type, parameters, picked = {}) {
    const values = {};

    for (const p of parameters) {
        const raw = Number(picked[p.key] ?? p.default);
        values[p.key] = Math.max(p.min, Math.min(p.max, Number.isFinite(raw) ? raw : p.default));
    }

    return SHAPES[type].fix(values);
}
