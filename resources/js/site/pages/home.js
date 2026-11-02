import { accentColor, prefersReducedMotion } from '../core/env.js';
import { startParticleShapes } from '../effects/particle-shapes.js';
import { buildRoller } from '../effects/roller-model.js';

/** Hero: the turning roller follows the cursor and drifts away with the scroll. */
export function heroRoller() {
    return {
        init() {
            buildRoller(this.$refs.spin, {
                length: 560,
                radius: 120,
                shaftRadius: 30,
                shaftExtra: 180,
                color: accentColor(),
                cap: { hub: 22, ring: 30, ringColor: '#1d2228' },
            });

            this.onScroll = () => {
                if (this.queued) return;
                this.queued = true;
                requestAnimationFrame(() => {
                    this.queued = false;
                    this.$root.style.setProperty('--scroll', Math.min(window.scrollY, 900));
                });
            };
            window.addEventListener('scroll', this.onScroll, { passive: true });
        },

        destroy() {
            window.removeEventListener('scroll', this.onScroll);
        },

        move(event) {
            if (prefersReducedMotion()) return;
            const box = this.$root.getBoundingClientRect();
            this.$root.style.setProperty('--mx', ((event.clientX - box.left) / box.width - 0.5).toFixed(3));
            this.$root.style.setProperty('--my', ((event.clientY - box.top) / box.height - 0.5).toFixed(3));
        },

        reset() {
            this.$root.style.setProperty('--mx', 0);
            this.$root.style.setProperty('--my', 0);
        },
    };
}

/** «What we make»: steps change by themselves every 4.5 s (paused under the cursor or off screen). */
const DWELL = 4500;

export function productStory({ count }) {
    return {
        step: 0,
        previous: 0,
        hold: false,
        elapsed: 0,

        init() {
            this.shapes = startParticleShapes(this.$refs.canvas, {
                onFrame: (dt, inView) => {
                    if (!dt || !inView || this.hold) return;
                    this.elapsed += dt;
                    if (this.elapsed >= DWELL) this.go(this.step + 1);
                },
            });
        },

        destroy() {
            this.shapes?.stop();
        },

        go(index) {
            this.previous = this.step;
            this.step = (index + count) % count;
            this.elapsed = 0;
            this.shapes?.show(this.step);
            this.restartBar();
        },

        restartBar() {
            const bar = this.$refs.bar;
            if (!bar) return;
            bar.style.animation = 'none';
            void bar.offsetWidth; // reflow, so the animation starts again
            bar.style.animation = '';
        },

        stepClass(index) {
            return { 'is-current': index === this.step, 'is-past': index < this.step };
        },
    };
}
