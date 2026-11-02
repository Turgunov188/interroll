import { t } from '../core/i18n.js';
import { LeadError, sendLead } from '../core/leads.js';
import { toast } from '../core/toast.js';

/**
 * Every lead form of the site. The form is a plain HTML form with named fields
 * (resources/views/components/site/lead-form.blade.php), so it also works without JavaScript;
 * this component sends it in the background and shows the result in place.
 *
 *   x-data="leadForm({ success: true })"   success: replace the form with the «sent» panel,
 *                                           otherwise a toast is shown and the form is reset.
 * Page components can add data right before sending by listening to "lead:collect"
 * on the form: event.detail.data is the FormData that will be sent.
 */
export default (options = {}) => ({
    status: 'idle',
    errors: {},
    number: null,
    files: [],
    dragging: false,

    get sending() {
        return this.status === 'sending';
    },

    get sent() {
        return this.status === 'sent';
    },

    error(field) {
        return this.errors[field]?.[0] ?? '';
    },

    pickFiles(event) {
        this.files = Array.from(event.target.files || []).map((file) => file.name);
    },

    dropFiles(event) {
        this.dragging = false;
        const input = this.$root.querySelector('input[type="file"]');
        if (!input || !event.dataTransfer?.files?.length) return;
        input.files = event.dataTransfer.files;
        this.pickFiles({ target: input });
    },

    async submit(event) {
        const form = event.target;
        const data = new FormData(form);

        form.dispatchEvent(new CustomEvent('lead:collect', { detail: { data } }));

        if (String(data.get('phone') || '').replace(/\D/g, '').length < 7) {
            this.errors = { phone: [t('lead.phone_required')] };
            form.querySelector('[name="phone"]')?.focus();
            return;
        }

        this.status = 'sending';
        this.errors = {};

        try {
            this.number = await sendLead(form.action, data);
            this.status = 'sent';
            form.reset();
            this.files = [];
            this.$dispatch('lead:sent', { number: this.number });

            if (!options.success) {
                toast(t('lead.sent'));
                this.status = 'idle';
            }
        } catch (e) {
            this.status = 'idle';
            if (e instanceof LeadError && e.message === 'invalid') {
                this.errors = e.fields;
                toast(t('lead.invalid'), { error: true });
            } else {
                toast(t(e?.message === 'throttled' ? 'lead.throttled' : 'lead.failed'), { error: true });
            }
        }
    },

    again() {
        this.status = 'idle';
        this.number = null;
    },
});
