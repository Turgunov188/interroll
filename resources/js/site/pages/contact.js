/**
 * «Сейчас открыто / закрыто» from the factory hours, by the Tashkent clock
 * (the visitor may be anywhere). Hours come from the site settings.
 */
const TIMEZONE = 'Asia/Tashkent';
const pad = (n) => String(n).padStart(2, '0');

function tashkentNow() {
    try {
        const parts = new Intl.DateTimeFormat('en-GB', { timeZone: TIMEZONE, hour: '2-digit', minute: '2-digit', weekday: 'short', hour12: false }).formatToParts(new Date());
        const get = (type) => parts.find((p) => p.type === type)?.value;
        return { hour: Number(get('hour')) % 24, minute: Number(get('minute')), day: get('weekday') };
    } catch {
        return null;
    }
}

export function openingStatus({ open, close, texts }) {
    return {
        now: null,

        init() {
            this.tick();
            this.timer = setInterval(() => this.tick(), 30000);
        },

        destroy() {
            clearInterval(this.timer);
        },

        tick() {
            this.now = tashkentNow();
        },

        get day() {
            return this.now?.day ?? '';
        },

        get isWeekday() {
            return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(this.day);
        },

        get minutes() {
            return this.now ? this.now.hour * 60 + this.now.minute : 0;
        },

        get isOpen() {
            return !!this.now && this.day !== 'Sun' && this.minutes >= open * 60 && this.minutes < close * 60;
        },

        get dot() {
            if (!this.now) return '#8a939d';
            return this.isOpen ? '#2e9e5b' : '#c9542e';
        },

        get title() {
            if (!this.now) return texts.hours;
            return this.isOpen ? texts.open : texts.closed;
        },

        get subtitle() {
            if (!this.now) return `${pad(open)}:00 – ${pad(close)}:00`;
            if (this.isOpen) {
                const left = close * 60 - this.minutes;
                return texts.until_close.replace(':h', Math.floor(left / 60)).replace(':m', left % 60);
            }
            const day = this.day !== 'Sun' && this.minutes < open * 60 ? texts.today : (this.day === 'Sat' || this.day === 'Sun' ? texts.monday : texts.tomorrow);
            return texts.opens.replace(':day', day).replace(':time', `${pad(open)}:00`);
        },

        get clockLine() {
            return this.now ? texts.clock.replace(':time', `${pad(this.now.hour)}:${pad(this.now.minute)}`) : '';
        },
    };
}

/** Contact form: topic and the way to answer; they are sent as details[topic], details[reply_via]. */
export function contactForm({ topics, replies, sent }) {
    return {
        topic: 'price',
        reply: 'phone',

        get hint() {
            return topics[this.topic]?.hint ?? '';
        },

        get sentText() {
            return sent.replace(':topic', topics[this.topic]?.label ?? '').replace(':reply', replies[this.reply] ?? '');
        },
    };
}
