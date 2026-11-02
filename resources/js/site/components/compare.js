/** Before / after slider: the range input moves the split line (CSS variable --split). */
export default (initial = 50) => ({
    split: initial,

    init() {
        this.$watch('split', (value) => this.$root.style.setProperty('--split', value));
        this.$root.style.setProperty('--split', this.split);
    },
});
