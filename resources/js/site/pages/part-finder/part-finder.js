import { recommend } from './recommend.js';

/**
 * Part finder wizard: five steps, a live recommendation on the side, the answers go into
 * the lead (details[…]). Rules: ./recommend.js; texts and products: part-finder.blade.php.
 */
const initial = () => ({
    step: 1,
    type: null,
    industry: null,
    load: 'mid',
    abrasion: 'mid',
    temperature: 30,
    oil: false,
    water: false,
    food: false,
});

export default (config) => ({
    ...initial(),

    get result() {
        return recommend(this);
    },

    get materialName() {
        return config.materials[this.result.material]?.name ?? '';
    },

    get note() {
        return config.notes[this.result.material] ?? '';
    },

    get reasons() {
        return this.result.reasons.map(({ key, temp }) =>
            (config.reasons[key] ?? '').replace(':temp', temp ?? this.temperature));
    },

    get products() {
        return config.suggestions[this.type ?? 'unknown'] ?? config.suggestions.unknown ?? [];
    },

    level(index) {
        return config.materials[this.result.material]?.levels[index] ?? 0;
    },

    levelName(index) {
        return config.levels[this.level(index)] ?? '';
    },

    get answers() {
        const env = ['oil', 'water', 'food'].filter((k) => this[k]).map((k) => config.environments[k]);

        return {
            part: config.types[this.type] ?? '—',
            industry: config.industries[this.industry] ?? '—',
            load: `${config.loads[this.load]} · ${config.abrasionWord} ${String(config.abrasions[this.abrasion]).toLowerCase()}`,
            environment: [`${this.temperature} °C`, ...env].join(' · '),
        };
    },

    get summary() {
        return `${this.materialName}, ${this.result.hardness} Shore A`;
    },

    // ---- navigation

    get canNext() {
        if (this.step === 1) return !!this.type;
        if (this.step === 2) return !!this.industry;
        return true;
    },

    canOpen(step) {
        return step <= this.step || (step === 2 && !!this.type) || (step === 3 && !!this.type && !!this.industry);
    },

    go(step) {
        if (step > this.step && !this.canNext) return;
        this.step = Math.max(1, Math.min(5, step));
        if (this.$root.getBoundingClientRect().top < 0) this.$root.scrollIntoView({ block: 'start' });
    },

    choose(field, value) {
        this[field] = value;
        if (field === 'industry' && value === 'food') this.food = true;
        this.go(this.step + 1);
    },

    progressClass(step) {
        return { 'is-current': this.step === step, 'is-done': this.step > step };
    },

    restart() {
        Object.assign(this, initial());
    },
});
