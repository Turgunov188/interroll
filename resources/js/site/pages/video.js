/**
 * /video player: playlist, YouTube (privacy-enhanced embed) or an uploaded file, chapters.
 * Nothing is loaded from YouTube until the visitor presses play.
 */
export default (videos) => ({
    videos,
    index: 0,
    playing: false,
    start: 0,
    progress: 0,

    get current() {
        return this.videos[this.index] ?? {};
    },

    get youtubeSrc() {
        return `https://www.youtube-nocookie.com/embed/${this.current.youtubeId}?autoplay=1&rel=0&start=${this.start}`;
    },

    select(index) {
        this.index = index;
        this.start = 0;
        this.progress = 0;
        this.playing = true;
    },

    play() {
        this.playing = true;
    },

    seek(seconds) {
        this.start = seconds;
        this.playing = true;
        this.$nextTick(() => {
            const video = this.$refs.video;
            if (video) {
                video.currentTime = seconds;
                video.play?.();
            }
        });
    },
});
