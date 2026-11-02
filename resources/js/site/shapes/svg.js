/**
 * Pieces of the technical drawings (SVG markup strings, viewBox 640 × 380): dimension lines,
 * axes, hatched sections. The drawing sheet sits on the blueprint grid of its container.
 */
export const INK = '#111418';
export const MUTED = '#5b636d';

export const round = (n) => Math.round(n * 10) / 10;

export const escapeXml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');

export const text = (x, y, label, { anchor = 'middle', size = 13, color = INK } = {}) =>
    `<text x="${round(x)}" y="${round(y)}" text-anchor="${anchor}" font-family="JetBrains Mono, monospace" font-size="${size}" fill="${color}">${escapeXml(label)}</text>`;

export const line = (x1, y1, x2, y2, { color = INK, extra = '' } = {}) =>
    `<line x1="${round(x1)}" y1="${round(y1)}" x2="${round(x2)}" y2="${round(y2)}" stroke="${color}" stroke-width="1" ${extra}/>`;

export const axis = (x1, y1, x2, y2) => line(x1, y1, x2, y2, { color: MUTED, extra: 'stroke-dasharray="14 4 2 4"' });

export const dimH = (x1, x2, y, label) =>
    line(x1, y, x2, y) + line(x1, y - 6, x1, y + 6) + line(x2, y - 6, x2, y + 6) + text((x1 + x2) / 2, y - 8, label);

export const dimV = (x, y1, y2, label) =>
    line(x, y1, x, y2) + line(x - 6, y1, x + 6, y1) + line(x - 6, y2, x + 6, y2)
    + `<text transform="translate(${round(x - 10)} ${round((y1 + y2) / 2)}) rotate(-90)" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="13" fill="${INK}">${escapeXml(label)}</text>`;

export const circle = (cx, cy, r, fill) =>
    `<circle cx="${round(cx)}" cy="${round(cy)}" r="${round(Math.max(r, 0.5))}" fill="${fill}" stroke="${INK}" stroke-width="1.6"/>`;

export const rect = (x, y, w, h, fill, { width = 1.6, opacity = null } = {}) =>
    `<rect x="${round(x)}" y="${round(y)}" width="${round(Math.max(w, 0.5))}" height="${round(Math.max(h, 0.5))}" fill="${fill}"${opacity === null ? '' : ` fill-opacity="${opacity}"`} stroke="${INK}" stroke-width="${width}"/>`;

export const polygon = (points, fill) =>
    `<polygon points="${points.map(([x, y]) => `${round(x)},${round(y)}`).join(' ')}" fill="${fill}" stroke="${INK}" stroke-width="1.6"/>`;

/** Hatch patterns: "h" for polyurethane sections, "core" for steel cores. */
const DEFS = '<defs>'
    + `<pattern id="h" width="7" height="7" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><rect width="7" height="7" fill="#fff"/><line x1="0" y1="0" x2="0" y2="7" stroke="${INK}" stroke-width="1.2"/></pattern>`
    + '<pattern id="core" width="8" height="8" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><rect width="8" height="8" fill="#d2d7dc"/><line x1="0" y1="0" x2="0" y2="8" stroke="#8a939d" stroke-width="2"/></pattern>'
    + '</defs>';

/** The whole drawing: parts + title block (stamp bottom-left, maker bottom-right, note top-left). */
export const sheet = (inner, { stamp = '', note = '', label = '' }) =>
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 380" width="100%" role="img" aria-label="${escapeXml(label)}">`
    + DEFS + inner
    + text(24, 360, stamp, { anchor: 'start', size: 11, color: MUTED })
    + text(616, 360, 'INTER ROLL', { anchor: 'end', size: 11, color: MUTED })
    + (note ? text(24, 32, note, { anchor: 'start', size: 12 }) : '')
    + '</svg>';
