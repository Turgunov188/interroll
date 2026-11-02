/**
 * Header: reading progress bar, logo turning with the scroll, the «Ещё» dropdown and the
 * burger menu on narrow screens (Escape and clicks outside close them).
 */
export default () => ({
    menuOpen: false,
    moreOpen: false,

    init() {
        let queued = false;
        const update = () => {
            queued = false;
            const y = window.scrollY;
            const max = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
            this.$root.style.setProperty('--progress', Math.min(1, y / max).toFixed(4));
            this.$root.style.setProperty('--logo-turn', `${Math.round(y * 0.4)}deg`);
        };

        this.onScroll = () => {
            if (!queued) {
                queued = true;
                requestAnimationFrame(update);
            }
        };

        window.addEventListener('scroll', this.onScroll, { passive: true });
        update();

        this.$watch('menuOpen', (open) => document.body.classList.toggle('has-open-menu', open));
    },

    destroy() {
        window.removeEventListener('scroll', this.onScroll);
    },

    toggleMenu() {
        this.menuOpen = !this.menuOpen;
    },

    close() {
        this.menuOpen = false;
        this.moreOpen = false;
    },
});
