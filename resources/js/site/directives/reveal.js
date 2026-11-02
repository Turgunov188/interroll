/**
 * x-reveal: the element (and every [data-reveal] inside it) gets .is-visible when it scrolls
 * into view; CSS does the animation (resources/css/site/components/motion.css).
 *   <section x-reveal> … <h2 data-reveal style="--delay: 100ms"> …
 * A modifier sets the threshold: x-reveal.30 = 30 % visible.
 */
export default function reveal(Alpine) {
    Alpine.directive('reveal', (el, { modifiers }, { cleanup }) => {
        const targets = el.matches('[data-reveal]') ? [el] : [];
        targets.push(...el.querySelectorAll('[data-reveal]'));

        const show = () => targets.forEach((target) => target.classList.add('is-visible'));

        if (!('IntersectionObserver' in window)) {
            show();
            return;
        }

        const threshold = Number(modifiers[0] ?? 12) / 100;
        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                show();
                observer.disconnect();
            }
        }, { threshold });

        observer.observe(el);
        cleanup(() => observer.disconnect());
    });
}
