/**
 * CSS 3D roller: a coated cylinder on a steel shaft, made of flat faces turned around the X axis.
 * Used by the home hero and the product configurator.
 *
 * @param {HTMLElement} host  element with transform-style: preserve-3d; the faces replace its children
 * @param {object} o
 * @param {number} o.length      coating length, px
 * @param {number} o.radius      coating radius, px
 * @param {number} o.shaftRadius px
 * @param {number} o.shaftExtra  how much longer the shaft is than the coating, px
 * @param {string} o.color       coating colour
 * @param {{hub: number, ring: number, ringColor: string}} o.cap  end face: hub and core ring, % of the radius
 */
export function buildRoller(host, options) {
    renderFaces(host, rollerFaces(options));
}

/** Put faces (CSS text of each) into a 3D host. */
export function renderFaces(host, faces, className = 'stage3d__face') {
    host.replaceChildren(...faces.map((css) => {
        const el = document.createElement('div');
        el.className = className;
        el.style.cssText = css;
        return el;
    }));
}

/** The roller as a list of face styles (see buildRoller for the options). */
export function rollerFaces({ length, radius, shaftRadius, shaftExtra, color, cap, faces = 32 }) {
    const parts = [];
    const add = (css) => parts.push(css);

    const shaftFaces = 12;
    const shaftHeight = Math.ceil((2 * Math.PI * shaftRadius) / shaftFaces) + 1;
    for (let i = 0; i < shaftFaces; i++) {
        add(`left:${-shaftExtra / 2}px;top:${-shaftHeight / 2}px;width:${length + shaftExtra}px;height:${shaftHeight}px;`
            + `transform:rotateX(${(i * 360) / shaftFaces}deg) translateZ(${shaftRadius}px);background:${i % 2 ? '#2a3038' : '#4a535e'}`);
    }

    // coating, with darker stripes so the turning is visible
    const faceHeight = Math.ceil((2 * Math.PI * radius) / faces) + 1;
    for (let i = 0; i < faces; i++) {
        const shade = i % 8 === 0 ? 0.32 : i % 2 ? 0.07 : 0;
        add(`left:0;top:${-faceHeight / 2}px;width:${length}px;height:${faceHeight}px;`
            + `transform:rotateX(${(i * 360) / faces}deg) translateZ(${radius}px);`
            + `background:linear-gradient(rgba(17,20,24,${shade}),rgba(17,20,24,${shade})),${color}`);
    }

    const hub = cap.hub.toFixed(1);
    const ring = cap.ring.toFixed(1);
    for (const dx of [-length / 2, length / 2]) {
        add(`left:${length / 2 - radius}px;top:${-radius}px;width:${2 * radius}px;height:${2 * radius}px;border-radius:50%;`
            + `background:radial-gradient(circle,#4a535e 0 ${hub}%,${cap.ringColor} ${(+hub + 1).toFixed(1)}% ${ring}%,${color} ${(+ring + 1).toFixed(1)}% 100%);`
            + `box-shadow:inset 0 0 0 2px rgba(17,20,24,.35);transform:translateX(${dx}px) rotateY(90deg)`);
    }

    return parts;
}
