/**
 * Part finder rules: answers → coating material, Shore A range and the reasons.
 * Pure function (no DOM, no texts), so it is covered by unit tests (recommend.test.js).
 *
 * @param {{ industry: string|null, load: 'low'|'mid'|'high', abrasion: 'low'|'mid'|'high',
 *           temperature: number, oil: boolean, water: boolean, food: boolean }} a
 * @returns {{ material: 'pu'|'nbr'|'epdm'|'sil', hardness: string, reasons: Array<{ key: string, temp?: number }> }}
 */
export function recommend(a) {
    const reasons = [];
    let material = 'pu';

    if (a.temperature > 120) {
        material = 'sil';
        reasons.push({ key: 'hot', temp: a.temperature });
    } else if (a.water && a.temperature > 60) {
        material = 'epdm';
        reasons.push({ key: 'steam' });
    } else if (a.oil && a.abrasion === 'low' && a.load !== 'high') {
        material = 'nbr';
        reasons.push({ key: 'oil_light' });
    } else {
        reasons.push({ key: 'pu' });
        if (a.oil) reasons.push({ key: 'pu_oil' });
    }

    if (a.food) reasons.push({ key: 'food' });
    if (a.temperature < -20) reasons.push({ key: 'cold', temp: a.temperature });

    let hardness = '85–90';
    if (a.industry === 'print') hardness = '60–75';
    else if (a.load === 'high') hardness = '92–95';
    else if (a.load === 'low') hardness = '70–80';
    if (material === 'sil') hardness = '40–70';
    if (material === 'epdm' || material === 'nbr') hardness = a.load === 'high' ? '80–85' : '65–75';

    reasons.push({ key: `load_${a.load}` });

    return { material, hardness, reasons };
}
