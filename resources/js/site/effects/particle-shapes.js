import { accentColor, animationLoop, fitCanvas, hexToRgb, prefersReducedMotion } from '../core/env.js';

/**
 * Home page «What we make»: 900 particles that flow into the outline of the current product
 * (roller, wheel, coupling spider, spring, custom part), turning slowly in 3D. The cursor pushes
 * them away, a click scatters them. Shapes are in unit coordinates and scaled to the canvas.
 */
const COUNT = 900;
const PUSH_RADIUS = 140;
const TAU = Math.PI * 2;

const line = (ax, ay, bx, by) => (u) => [ax + (bx - ax) * u, ay + (by - ay) * u];
const ellipse = (cx, cy, rx, ry) => (u) => [cx + Math.cos(u * TAU) * rx, cy + Math.sin(u * TAU) * ry];
const disc = (cx, cy, r0, r1) => () => {
    const a = Math.random() * TAU;
    const r = Math.sqrt(r0 * r0 + Math.random() * (r1 * r1 - r0 * r0));
    return [cx + Math.cos(a) * r, cy + Math.sin(a) * r];
};

/** Spread COUNT points over primitives [weight, generator(u)]. */
function sample(primitives) {
    const total = primitives.reduce((sum, [weight]) => sum + weight, 0);
    const points = [];

    primitives.forEach(([weight, generate], k) => {
        const n = k === primitives.length - 1 ? COUNT - points.length : Math.round((COUNT * weight) / total);
        for (let i = 0; i < n; i++) points.push(generate((i + 0.5) / n));
    });

    return points.slice(0, COUNT);
}

const SHAPES = [
    // roller with shaft ends
    () => sample([
        [2, line(-1, -0.42, 1, -0.42)], [2, line(-1, 0.42, 1, 0.42)],
        [1.1, ellipse(-1, 0, 0.13, 0.42)], [1.1, ellipse(1, 0, 0.13, 0.42)],
        [3, () => [-1 + Math.random() * 2, -0.4 + Math.random() * 0.8]],
        [0.35, line(-1.35, -0.07, -1.13, -0.07)], [0.35, line(-1.35, 0.07, -1.13, 0.07)],
        [0.35, line(1.13, -0.07, 1.35, -0.07)], [0.35, line(1.13, 0.07, 1.35, 0.07)],
    ]),
    // wheel with spokes
    () => sample([
        [5, disc(0, 0, 0.74, 0.96)], [2, ellipse(0, 0, 0.74, 0.74)], [1.2, disc(0, 0, 0.1, 0.24)],
        [0.5, line(0.24, 0, 0.74, 0)], [0.5, line(-0.24, 0, -0.74, 0)],
        [0.5, line(0.12, 0.21, 0.37, 0.64)], [0.5, line(-0.12, -0.21, -0.37, -0.64)],
        [0.5, line(-0.12, 0.21, -0.37, 0.64)], [0.5, line(0.12, -0.21, 0.37, -0.64)],
    ]),
    // coupling spider
    () => sample([
        [5, () => {
            const a = Math.random() * TAU;
            const r = Math.sqrt(0.08 + Math.random() * 0.92) * (0.62 + 0.3 * Math.cos(6 * a));
            return [Math.cos(a) * r, Math.sin(a) * r];
        }],
        [1.4, ellipse(0, 0, 0.18, 0.18)],
    ]),
    // die spring
    () => sample([
        [7, (u) => [-1 + 2 * u, 0.5 * Math.sin(u * TAU * 6.5) + (Math.random() - 0.5) * 0.06]],
        [0.6, line(-1.05, -0.5, -1.05, 0.5)], [0.6, line(1.05, -0.5, 1.05, 0.5)],
    ]),
    // custom part
    () => sample([
        [5, disc(0, 0, 0.72, 0.98)], [2.2, disc(0, 0, 0.18, 0.42)], [1, line(0, -0.98, 0, -0.42)],
    ]),
];

/**
 * @param {HTMLCanvasElement} canvas
 * @param {{ onFrame?: (dt: number, inView: boolean) => void }} hooks  called every frame
 * @returns {{ show(index: number): void, stop(): void }}
 */
export function startParticleShapes(canvas, hooks = {}) {
    const ctx = canvas.getContext?.('2d');
    if (!ctx) return { show() {}, stop() {} };

    const still = prefersReducedMotion();
    const shapes = SHAPES.map((build) => build());
    const accent = hexToRgb(accentColor());
    const pointer = { x: -9999, y: -9999, on: false };
    const particles = Array.from({ length: COUNT }, (_, i) => ({
        x: Math.random() * 1200,
        y: Math.random() * 760,
        vx: 0,
        vy: 0,
        phase: Math.random() * TAU,
        lit: i % 4 === 0,
    }));

    let width = 0;
    let height = 0;
    let current = 0;
    let swirl = 0;

    const resize = () => { [width, height] = fitCanvas(canvas, ctx); };
    const local = (e) => {
        const box = canvas.getBoundingClientRect();
        return [e.clientX - box.left, e.clientY - box.top, box];
    };
    const onMove = (e) => {
        const [x, y, box] = local(e);
        Object.assign(pointer, { x, y, on: x >= 0 && y >= 0 && x <= box.width && y <= box.height });
    };
    const onLeave = () => { pointer.on = false; };
    const onDown = (e) => {
        const [x, y, box] = local(e);
        if (x < 0 || y < 0 || x > box.width || y > box.height || e.target.closest?.('button, a')) return;
        for (const p of particles) {
            const dx = p.x - x;
            const dy = p.y - y;
            const d = Math.hypot(dx, dy) || 1;
            if (d < 320) {
                const f = 1 - d / 320;
                p.vx += (dx / d) * f * 24;
                p.vy += (dy / d) * f * 24;
            }
        }
    };

    window.addEventListener('mousemove', onMove, { passive: true });
    window.addEventListener('mousedown', onDown);
    window.addEventListener('resize', resize);
    document.addEventListener('mouseleave', onLeave);
    resize();

    const frame = (time, dt) => {
        if (canvas.clientWidth !== width || canvas.clientHeight !== height) resize();

        const box = canvas.getBoundingClientRect();
        hooks.onFrame?.(still ? 0 : dt, box.bottom > 0 && box.top < window.innerHeight);

        const t = still ? 0 : time;
        const shape = shapes[current];
        const wide = width > 900;
        const cx = wide ? width * 0.66 : width * 0.5;
        const cy = wide ? height * 0.5 : height * 0.78;
        const scale = Math.min(wide ? width * 0.2 : width * 0.36, height * (wide ? 0.34 : 0.16));
        const yaw = still ? 0.35 : Math.sin(t * 0.0005) * 0.95;
        const cosYaw = Math.cos(yaw);
        const sinYaw = Math.sin(yaw);
        const rot = still ? 0 : Math.sin(t * 0.0003) * 0.1;
        const cosRot = Math.cos(rot);
        const sinRot = Math.sin(rot);
        const [ar, ag, ab] = accent;

        swirl *= 0.965;
        ctx.clearRect(0, 0, width, height);

        for (let i = 0; i < COUNT; i++) {
            const p = particles[i];
            const [sx0, sy0] = shape[i];
            const sx = sx0 * cosRot - sy0 * sinRot;
            const sy = sx0 * sinRot + sy0 * cosRot;
            const z = sx * sinYaw;
            let tx = cx + sx * cosYaw * scale;
            let ty = cy + sy * scale;

            if (!still) {
                tx += Math.sin(t * 0.002 + p.phase) * 1.3;
                ty += Math.cos(t * 0.0017 + p.phase) * 1.3;
            }

            p.vx += (tx - p.x) * 0.014;
            p.vy += (ty - p.y) * 0.014;

            if (swirl > 0.02) {
                const dx = p.x - cx;
                const dy = p.y - cy;
                const d = Math.hypot(dx, dy) || 1;
                p.vx += (-dy / d) * swirl * 1.6;
                p.vy += (dx / d) * swirl * 1.6;
            }

            let near = 0;
            if (pointer.on) {
                const dx = p.x - pointer.x;
                const dy = p.y - pointer.y;
                const d = Math.hypot(dx, dy) || 1;
                if (d < PUSH_RADIUS) {
                    near = 1 - d / PUSH_RADIUS;
                    p.vx += (dx / d) * near * near * 5;
                    p.vy += (dy / d) * near * near * 5;
                }
            }

            p.vx *= 0.86;
            p.vy *= 0.86;
            p.x += p.vx;
            p.y += p.vy;

            const speed = Math.min(1, Math.hypot(p.vx, p.vy) / 6);
            const depth = 1 + z * 0.35;
            const k = Math.max(near, p.lit ? 0.9 : 0);
            const r = Math.round(30 + (ar - 30) * k);
            const g = Math.round(36 + (ag - 36) * k);
            const b = Math.round(44 + (ab - 44) * k);
            const alpha = Math.min(0.95, 0.42 + z * 0.22 + speed * 0.3 + near * 0.15);

            ctx.fillStyle = `rgba(${r},${g},${b},${alpha.toFixed(2)})`;
            ctx.beginPath();
            ctx.arc(p.x, p.y, Math.max(0.6, (1.5 + near * 1.8 + speed * 0.8) * depth), 0, TAU);
            ctx.fill();
        }
    };

    const stopLoop = animationLoop(frame);

    return {
        show(index) {
            if (index === current) return;
            current = ((index % shapes.length) + shapes.length) % shapes.length;
            swirl = 1;
            for (const p of particles) {
                p.vx += (Math.random() - 0.5) * 10;
                p.vy += (Math.random() - 0.5) * 10;
            }
        },
        stop() {
            stopLoop();
            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mousedown', onDown);
            window.removeEventListener('resize', resize);
            document.removeEventListener('mouseleave', onLeave);
        },
    };
}
