/** Small browser helpers shared by the site scripts. */

export const prefersReducedMotion = () =>
    typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

/** "#F5B700" → [245, 183, 0] */
export function hexToRgb(hex, fallback = [245, 183, 0]) {
    const raw = String(hex || '').trim().replace('#', '');
    const full = raw.length === 3 ? raw.split('').map((c) => c + c).join('') : raw;
    const n = Number.parseInt(full, 16);

    return Number.isNaN(n) || full.length !== 6 ? fallback : [(n >> 16) & 255, (n >> 8) & 255, n & 255];
}

/** Accent colour of the design system (CSS custom property). */
export const accentColor = () =>
    getComputedStyle(document.documentElement).getPropertyValue('--c-accent').trim() || '#f5b700';

/** 1234567 → "1 234 567" */
export const formatNumber = (value) => String(Math.round(value)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

/**
 * requestAnimationFrame loop that stops while the tab is hidden.
 * Returns a function that stops the loop for good.
 */
export function animationLoop(frame) {
    let id = 0;
    let last = 0;
    let running = true;

    const tick = (time) => {
        const dt = last ? Math.min(64, time - last) : 16;
        last = time;
        frame(time, dt);
        id = requestAnimationFrame(tick);
    };

    const onVisibility = () => {
        if (!running) return;
        if (document.hidden) {
            cancelAnimationFrame(id);
        } else {
            last = 0;
            id = requestAnimationFrame(tick);
        }
    };

    document.addEventListener('visibilitychange', onVisibility);
    id = requestAnimationFrame(tick);

    return () => {
        running = false;
        cancelAnimationFrame(id);
        document.removeEventListener('visibilitychange', onVisibility);
    };
}

/** Canvas sized to its CSS box with the device pixel ratio (max 2). Returns [width, height]. */
export function fitCanvas(canvas, context) {
    const dpr = Math.min(2, window.devicePixelRatio || 1);
    const width = canvas.clientWidth;
    const height = canvas.clientHeight;

    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    context.setTransform(dpr, 0, 0, dpr, 0, 0);

    return [width, height];
}
