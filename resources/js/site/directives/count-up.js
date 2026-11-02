import { formatNumber, prefersReducedMotion } from '../core/env.js';

/**
 * x-count-up="1200": counts from 0 to the number when it scrolls into view.
 * Values that are not numbers yet (a gap "[__]") are left as they are.
 */
const DURATION = 1400;

export default function countUp(Alpine) {
    Alpine.directive('count-up', (el, { expression }, { evaluate, cleanup }) => {
        const target = Number(evaluate(expression));

        if (!Number.isFinite(target) || target <= 0) return;

        if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
            el.textContent = formatNumber(target);
            return;
        }

        el.textContent = '0';

        const run = () => {
            const start = performance.now();
            const step = (now) => {
                const p = Math.min(1, (now - start) / DURATION);
                el.textContent = formatNumber(target * (1 - (1 - p) ** 3));
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };

        const observer = new IntersectionObserver((entries) => {
            if (entries.some((e) => e.isIntersecting)) {
                run();
                observer.disconnect();
            }
        }, { threshold: 0.4 });

        observer.observe(el);
        cleanup(() => observer.disconnect());
    });
}
