/**
 * Texts the scripts need, in the page language. The layout prints them once as JSON
 * (<script id="site-i18n">, from lang/{locale}/js.php).
 */
let messages = null;

function load() {
    if (messages) return messages;

    try {
        messages = JSON.parse(document.getElementById('site-i18n')?.textContent || '{}');
    } catch {
        messages = {};
    }

    return messages;
}

/** t('lead.sent') → the text; ":name" placeholders are filled from replace. */
export function t(key, replace = {}) {
    const value = key.split('.').reduce((node, part) => (node && typeof node === 'object' ? node[part] : undefined), load());

    if (typeof value !== 'string') return key;

    return Object.entries(replace).reduce((text, [name, v]) => text.replaceAll(`:${name}`, String(v)), value);
}

export const locale = () => document.documentElement.lang || 'ru';
