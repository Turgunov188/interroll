/** "Копировать" buttons: copy a text, say so for a moment. */
export default (text = '') => ({
    copied: false,

    async copy(value = text) {
        try {
            await navigator.clipboard?.writeText(value);
        } catch {
            // clipboard blocked (http, old browser): the text is still visible on the page
        }

        this.copied = true;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => { this.copied = false; }, 1800);
    },
});
