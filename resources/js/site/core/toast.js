/** Short status messages at the bottom of the screen (form sent, form failed). */
const DURATION = 3200;

function container() {
    let box = document.querySelector('.toasts');

    if (!box) {
        box = document.createElement('div');
        box.className = 'toasts';
        box.setAttribute('role', 'status');
        box.setAttribute('aria-live', 'polite');
        document.body.append(box);
    }

    return box;
}

export function toast(message, { error = false } = {}) {
    const item = document.createElement('div');
    item.className = error ? 'toast toast--error' : 'toast';
    item.textContent = message;
    container().append(item);

    setTimeout(() => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(8px)';
    }, DURATION);
    setTimeout(() => item.remove(), DURATION + 450);
}
