/** Article: the table of contents follows the section being read. */
export function tableOfContents() {
    return {
        current: null,

        init() {
            const headings = this.$refs.body?.querySelectorAll('h2[id]') ?? [];
            if (!headings.length || !('IntersectionObserver' in window)) return;

            this.current = headings[0].id;
            this.observer = new IntersectionObserver((entries) => {
                const visible = entries.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
                if (visible.length) this.current = visible[0].target.id;
            }, { rootMargin: '-80px 0px -60% 0px' });

            headings.forEach((h) => this.observer.observe(h));
        },

        destroy() {
            this.observer?.disconnect();
        },
    };
}

/** Shore A scale of the hardness article: compares the value with familiar things. */
export function shoreScale(points) {
    return {
        value: 85,

        get point() {
            return points.reduce((found, p) => (this.value >= p[0] - 6 ? p : found), points[0]);
        },

        get position() {
            return (((this.value - 20) / 78) * 100).toFixed(1);
        },
    };
}
