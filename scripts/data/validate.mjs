// Validate database/data/providers.json (schema, categories, Belgian coordinates, unique source_ref).
//   node scripts/data/validate.mjs        -> exits 1 on the first error
import { readFileSync } from 'node:fs';
import { join } from 'node:path';
import assert from 'node:assert/strict';

export const CATEGORIES = ['toiletteur-salon', 'toiletteur-domicile', 'veterinaire', 'urgences-24h', 'osteopathe', 'kine-physio',
  'hydrotherapie', 'dentisterie', 'educateur', 'comportementaliste', 'pet-sitter', 'promeneur', 'garderie', 'pension',
  'eleveur-agree', 'refuge', 'animalerie', 'lieu-dog-friendly', 'parc-a-chiens'];
const KEYS = ['source', 'source_ref', 'name', 'categories', 'street', 'house_number', 'postal_code', 'city', 'region', 'lat', 'lng',
  'phone', 'email', 'website', 'opening_hours', 'is_mobile', 'bce_number', 'regulatory_number', 'license', 'fetched_at', 'commune'];
const strOrNull = v => v === null || (typeof v === 'string' && v.length > 0);

export function validate(rows) {
  assert.ok(Array.isArray(rows) && rows.length > 0, 'providers must be a non-empty array');
  const refs = new Set();
  for (const r of rows) {
    const id = `${r.source}:${r.source_ref}`;
    assert.deepEqual(Object.keys(r).sort(), [...KEYS].sort(), `${id}: keys`);
    assert.ok(['osm', 'bce', 'registry'].includes(r.source), `${id}: source`);
    assert.ok(!refs.has(id), `${id}: duplicate source_ref`); refs.add(id);
    assert.ok(typeof r.name === 'string' && r.name.trim(), `${id}: name`);
    assert.ok(r.categories.length && r.categories.every(c => CATEGORIES.includes(c)), `${id}: categories ${r.categories}`);
    assert.ok(r.postal_code === null || /^[1-9]\d{3}$/.test(r.postal_code), `${id}: postal_code`);
    assert.ok([null, 'wallonia', 'flanders', 'brussels'].includes(r.region), `${id}: region`);
    assert.ok((r.lat === null) === (r.lng === null), `${id}: lat/lng pair`);
    if (r.lat !== null) assert.ok(r.lat >= 49.49 && r.lat <= 51.51 && r.lng >= 2.54 && r.lng <= 6.41, `${id}: outside Belgium`);
    assert.ok(r.phone === null || /^\+[1-9]\d{7,14}$/.test(r.phone), `${id}: phone ${r.phone}`);
    assert.ok(r.email === null || /^[^\s@]+@[^\s@]+$/.test(r.email), `${id}: email`);
    assert.ok(r.website === null || /^https?:\/\//.test(r.website), `${id}: website`);
    assert.ok(r.bce_number === null || /^[01]\d{3}\.\d{3}\.\d{3}$/.test(r.bce_number), `${id}: bce_number`);
    assert.equal(typeof r.is_mobile, 'boolean', `${id}: is_mobile`);
    assert.ok(r.commune === null || /^[\w-]+$/.test(r.commune), `${id}: commune`);
    for (const k of ['street', 'house_number', 'city', 'opening_hours', 'regulatory_number']) assert.ok(strOrNull(r[k]), `${id}: ${k}`);
    assert.ok(r.license && !Number.isNaN(Date.parse(r.fetched_at)), `${id}: license/fetched_at`);
  }
  return true;
}

if (import.meta.main) {
  const file = join(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1'), 'database/data/providers.json');
  const rows = JSON.parse(readFileSync(file, 'utf8'));
  validate(rows);
  console.log(`OK: ${rows.length} providers valid`);
}
