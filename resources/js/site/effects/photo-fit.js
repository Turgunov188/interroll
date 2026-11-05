/**
 * Product photos come in two kinds: a cut-out part on a white background (catalogue scans) and
 * a regular photo (a shelf of wheels, a workshop). The first must be shown whole, the second
 * should fill its slot. Images marked data-fit="auto" start as «contain»; once loaded, their
 * edges are sampled and a photo whose edges are not white switches to «cover».
 */
const SIZE = 24;
const WHITE = 232; // every channel above this counts as background
const SHARE = 0.85; // this share of edge pixels must be white for a cut-out

const canvas = document.createElement('canvas');
canvas.width = canvas.height = SIZE;
const ctx = canvas.getContext('2d', { willReadFrequently: true });

const isCutOut = (img) => {
    ctx.clearRect(0, 0, SIZE, SIZE);
    ctx.drawImage(img, 0, 0, SIZE, SIZE);
    const { data } = ctx.getImageData(0, 0, SIZE, SIZE);

    let white = 0;
    let total = 0;
    for (let y = 0; y < SIZE; y++) {
        for (let x = 0; x < SIZE; x++) {
            if (x > 0 && x < SIZE - 1 && y > 0 && y < SIZE - 1) continue; // edges only
            const i = (y * SIZE + x) * 4;
            const [r, g, b, a] = [data[i], data[i + 1], data[i + 2], data[i + 3]];
            total++;
            if (a < 16 || (r > WHITE && g > WHITE && b > WHITE)) white++;
        }
    }

    return white / total >= SHARE;
};

const decide = (img) => {
    if (img.dataset.fit !== 'auto' || !img.naturalWidth) return;
    try {
        img.dataset.fit = isCutOut(img) ? 'contain' : 'cover';
    } catch {
        img.dataset.fit = 'contain'; // a picture from another origin cannot be read: keep it whole
    }
};

export function startPhotoFit(root = document) {
    // load does not bubble, but it can be caught on the way down
    root.addEventListener('load', (event) => {
        if (event.target instanceof HTMLImageElement) decide(event.target);
    }, true);

    root.querySelectorAll('img[data-fit="auto"]').forEach((img) => img.complete && decide(img));
}
