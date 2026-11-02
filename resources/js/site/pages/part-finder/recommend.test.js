import assert from 'node:assert/strict';
import { test } from 'node:test';
import { recommend } from './recommend.js';

const base = { industry: 'metal', load: 'mid', abrasion: 'mid', temperature: 30, oil: false, water: false, food: false };

test('polyurethane is the default for ordinary conditions', () => {
    const r = recommend(base);
    assert.equal(r.material, 'pu');
    assert.equal(r.hardness, '85–90');
    assert.deepEqual(r.reasons.map((x) => x.key), ['pu', 'load_mid']);
});

test('high temperature needs silicone', () => {
    const r = recommend({ ...base, temperature: 150 });
    assert.equal(r.material, 'sil');
    assert.equal(r.hardness, '40–70');
    assert.equal(r.reasons[0].temp, 150);
});

test('hot water or steam needs EPDM', () => {
    assert.equal(recommend({ ...base, water: true, temperature: 80 }).material, 'epdm');
    assert.equal(recommend({ ...base, water: true, temperature: 40 }).material, 'pu');
});

test('oil with light duty is fine on NBR, heavy duty stays polyurethane', () => {
    assert.equal(recommend({ ...base, oil: true, abrasion: 'low', load: 'low' }).material, 'nbr');
    const heavy = recommend({ ...base, oil: true, abrasion: 'high', load: 'high' });
    assert.equal(heavy.material, 'pu');
    assert.equal(heavy.hardness, '92–95');
    assert.ok(heavy.reasons.some((x) => x.key === 'pu_oil'));
});

test('printing rollers are soft', () => {
    assert.equal(recommend({ ...base, industry: 'print' }).hardness, '60–75');
});

test('food contact and frost add their reasons', () => {
    const keys = recommend({ ...base, food: true, temperature: -30 }).reasons.map((x) => x.key);
    assert.ok(keys.includes('food'));
    assert.ok(keys.includes('cold'));
});
