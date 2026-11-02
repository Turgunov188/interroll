/**
 * Filter of server-rendered cards by one group (projects by industry, articles by rubric).
 * items: [{ key, group }] in page order.
 */
export default ({ items }) => ({
    group: 'all',

    get shown() {
        return items.filter((item) => this.group === 'all' || item.group === this.group).map((item) => item.key);
    },

    isShown(key) {
        return this.shown.includes(key);
    },

    /** Cards appear one after another after a filter change. */
    delay(key) {
        return `${Math.max(0, this.shown.indexOf(key)) * 80}ms`;
    },

    count(group) {
        return group === 'all' ? items.length : items.filter((item) => item.group === group).length;
    },
});
