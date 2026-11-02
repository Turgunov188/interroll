import { accentColor, animationLoop, fitCanvas, hexToRgb, prefersReducedMotion } from '../core/env.js';

/**
 * The elastic grid behind every page: nodes follow a slow wave, the cursor pushes them,
 * a click sends a ripple and scrolling drags the grid. Displaced nodes light up in the accent colour.
 */
const GAP = 38;
const RADIUS = 170;

export function startMeshBackground(canvas) {
    const ctx = canvas.getContext?.('2d');
    if (!ctx) return () => {};

    const still = prefersReducedMotion();
    const pointer = { x: -9999, y: -9999, px: -9999, py: -9999, vx: 0, vy: 0, on: false };
    let width = 0;
    let height = 0;
    let cols = 0;
    let rows = 0;
    let nodes = [];
    let accent = hexToRgb(accentColor());

    const build = () => {
        [width, height] = fitCanvas(canvas, ctx);
        cols = Math.ceil(width / GAP) + 3;
        rows = Math.ceil(height / GAP) + 3;
        nodes = [];

        for (let j = 0; j < rows; j++) {
            for (let i = 0; i < cols; i++) {
                const rx = (i - 1) * GAP;
                const ry = (j - 1) * GAP;
                nodes.push({ rx, ry, x: rx, y: ry, vx: 0, vy: 0 });
            }
        }
    };

    const impulse = (cx, cy, radius, power) => {
        for (const n of nodes) {
            const dx = n.x - cx;
            const dy = n.y - cy;
            const d = Math.hypot(dx, dy) || 1;
            if (d < radius) {
                const f = 1 - d / radius;
                n.vx += (dx / d) * f * power;
                n.vy += (dy / d) * f * power;
            }
        }
    };

    let lastScroll = window.scrollY;
    const listeners = {
        mousemove: (e) => Object.assign(pointer, { x: e.clientX, y: e.clientY, on: true }),
        mousedown: (e) => impulse(e.clientX, e.clientY, 300, 14),
        scroll: () => {
            const y = window.scrollY;
            const d = Math.max(-60, Math.min(60, y - lastScroll));
            lastScroll = y;
            for (const n of nodes) n.vy -= d * 0.06 * (0.6 + 0.4 * Math.sin(n.rx * 0.01));
        },
        resize: build,
    };
    const onLeave = () => { pointer.on = false; };

    for (const [event, handler] of Object.entries(listeners)) {
        window.addEventListener(event, handler, { passive: true });
    }
    document.addEventListener('mouseleave', onLeave);
    build();

    const frame = (time) => {
        const t = still ? 0 : time;

        if (pointer.on && pointer.px > -9000) {
            pointer.vx = pointer.x - pointer.px;
            pointer.vy = pointer.y - pointer.py;
        } else {
            pointer.vx = 0;
            pointer.vy = 0;
        }
        pointer.px = pointer.x;
        pointer.py = pointer.y;

        for (const n of nodes) {
            const tx = n.rx + (still ? 0 : Math.sin(n.ry * 0.012 + t * 0.0011) * 3);
            const ty = n.ry + (still ? 0 : Math.sin(n.rx * 0.01 + t * 0.0013) * 4 + Math.cos(n.ry * 0.014 - t * 0.0009) * 2);
            n.vx += (tx - n.x) * 0.05;
            n.vy += (ty - n.y) * 0.05;

            if (pointer.on) {
                const dx = n.x - pointer.x;
                const dy = n.y - pointer.y;
                const d = Math.hypot(dx, dy) || 1;
                if (d < RADIUS) {
                    const f = (1 - d / RADIUS) ** 2;
                    n.vx += (dx / d) * f * 2.4 + pointer.vx * f * 0.22;
                    n.vy += (dy / d) * f * 2.4 + pointer.vy * f * 0.22;
                }
            }

            n.vx *= 0.88;
            n.vy *= 0.88;
            n.x += n.vx;
            n.y += n.vy;
        }

        draw();
    };

    const draw = () => {
        ctx.fillStyle = '#eef0f2';
        ctx.fillRect(0, 0, width, height);

        ctx.lineWidth = 1;
        ctx.strokeStyle = 'rgba(62,70,80,0.16)';
        ctx.beginPath();
        for (let j = 0; j < rows; j++) {
            for (let i = 0; i < cols; i++) {
                const n = nodes[j * cols + i];
                if (i === 0) ctx.moveTo(n.x, n.y);
                else ctx.lineTo(n.x, n.y);
            }
        }
        for (let i = 0; i < cols; i++) {
            for (let j = 0; j < rows; j++) {
                const n = nodes[j * cols + i];
                if (j === 0) ctx.moveTo(n.x, n.y);
                else ctx.lineTo(n.x, n.y);
            }
        }
        ctx.stroke();

        const [ar, ag, ab] = accent;
        for (let j = 0; j < rows; j++) {
            for (let i = 0; i < cols; i++) {
                const n = nodes[j * cols + i];
                const shift = Math.hypot(n.x - n.rx, n.y - n.ry);

                if (shift <= 5.5) {
                    ctx.fillStyle = 'rgba(62,70,80,0.35)';
                    ctx.fillRect(n.x - 1, n.y - 1, 2, 2);
                    continue;
                }

                const k = Math.min(1, (shift - 5.5) / 22);
                ctx.strokeStyle = `rgba(${ar},${ag},${ab},${(0.25 + k * 0.6).toFixed(2)})`;
                ctx.lineWidth = 1 + k * 1.6;
                if (i < cols - 1) {
                    const b = nodes[j * cols + i + 1];
                    ctx.beginPath();
                    ctx.moveTo(n.x, n.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
                if (j < rows - 1) {
                    const b = nodes[(j + 1) * cols + i];
                    ctx.beginPath();
                    ctx.moveTo(n.x, n.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
                ctx.fillStyle = `rgba(17,20,24,${(0.25 + k * 0.5).toFixed(2)})`;
                ctx.beginPath();
                ctx.arc(n.x, n.y, 1.2 + k * 2.2, 0, Math.PI * 2);
                ctx.fill();
            }
        }
    };

    const stop = animationLoop(frame);

    return () => {
        stop();
        for (const [event, handler] of Object.entries(listeners)) window.removeEventListener(event, handler);
        document.removeEventListener('mouseleave', onLeave);
    };
}
