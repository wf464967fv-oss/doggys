// Build database/data/providers.json from all legal sources, then validate it.
//
//   node scripts/data/build.mjs
//
// Inputs (fetched + cached on first run in database/data/raw/, gitignored; delete a file to refresh):
//   - OpenStreetMap via Overpass (ODbL)               -> raw/osm_pois.json, raw/osm_postal_codes.json
//   - Vlaanderen "Erkende dierenasielen" HTML table   -> raw/vl_asielen.html (free reuse, Bestuursdecreet art. II.55)
//   - Nominatim geocoding of registry addresses (ODbL) -> raw/nominatim.json
//   - optional database/data/bce_providers.json produced by scripts/data/bce_extract.mjs (KBO Open Data)
import { readFileSync, writeFileSync, existsSync, statSync } from 'node:fs';
import { join } from 'node:path';
import { overpass, ROOT, RAW } from './overpass.mjs';
import { POI_QUERY, POSTAL_QUERY } from './osm_fetch.mjs';
import { validate } from './validate.mjs';

const UA = 'Doggys data import (contact@doggys.be)';
const OUT = join(ROOT, 'database/data/providers.json');

// ---------- helpers ----------
export function regionFromPostal(pc) {
  const n = Number(pc);
  if (!Number.isInteger(n) || n < 1000 || n > 9999) return null;
  if (n <= 1299) return 'brussels';
  if (n <= 1499 || (n >= 4000 && n <= 7999)) return 'wallonia';
  return 'flanders';
}

export function e164(raw) {
  if (!raw) return null;
  let p = String(raw).split(/[;,]|\s{2,}|\bou\b|\bof\b/)[0].replace(/[\s.\-/()]/g, '');
  if (p.startsWith('00')) p = '+' + p.slice(2);
  else if (p.startsWith('0')) p = '+32' + p.slice(1);
  else if (/^32\d{8,9}$/.test(p)) p = '+' + p;
  p = p.replace(/^\+320/, '+32');
  if (/^\+32\d{8,9}$/.test(p)) return p;
  if (/^\+(?!32)[1-9]\d{7,14}$/.test(p)) return p; // foreign but valid E.164
  return null;
}

export const url = u => {
  if (!u) return null;
  u = String(u).split(';')[0].trim();
  if (!u) return null;
  if (!/^https?:\/\//i.test(u)) u = 'https://' + u.replace(/^\/+/, '');
  try { return new URL(u).href; } catch { return null; }
};
export const email = e => (e = String(e || '').split(';')[0].trim().toLowerCase()) && /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/.test(e) ? e : null;

export function bceFormat(v) {
  const d = String(v || '').replace(/\D/g, '');
  const n = d.length === 9 ? '0' + d : d;
  return /^[01]\d{9}$/.test(n) ? `${n.slice(0, 4)}.${n.slice(4, 7)}.${n.slice(7)}` : null;
}

export const normName = s => String(s || '').normalize('NFD').replace(/\p{M}/gu, '').toLowerCase()
  .replace(/\b(vzw|asbl|sprl|srl|bv|bvba|nv|sa|scrl|cv|commv)\b/g, '').replace(/[^a-z0-9]/g, '');

export function distanceM(a, b) {
  const R = 6371000, r = Math.PI / 180;
  const dLat = (b.lat - a.lat) * r, dLng = (b.lng - a.lng) * r;
  const h = Math.sin(dLat / 2) ** 2 + Math.cos(a.lat * r) * Math.cos(b.lat * r) * Math.sin(dLng / 2) ** 2;
  return 2 * R * Math.asin(Math.sqrt(h));
}

// ---------- postal codes (OSM boundary=postal_code polygons) ----------
function loadPostal(json) {
  const codes = [], cityOf = {};
  for (const rel of json.elements) {
    const code = rel.tags.postal_code;
    const label = rel.tags.description || rel.tags.note || '';
    const city = label.replace(/^\d{4}\s*/, '').trim() || null;
    if (!/^\d{4}$/.test(code || '')) continue;
    cityOf[code] ??= city;
    // Ray casting over all ring segments works without assembling rings (parity of crossings).
    const segs = [];
    for (const m of rel.members) if (m.type === 'way' && m.geometry)
      for (let i = 1; i < m.geometry.length; i++) segs.push([m.geometry[i - 1].lon, m.geometry[i - 1].lat, m.geometry[i].lon, m.geometry[i].lat]);
    codes.push({ code, city, b: rel.bounds, segs });
  }
  const at = (lat, lng) => {
    for (const p of codes) {
      if (lat < p.b.minlat || lat > p.b.maxlat || lng < p.b.minlon || lng > p.b.maxlon) continue;
      let inside = false;
      for (const [x1, y1, x2, y2] of p.segs)
        if ((y1 > lat) !== (y2 > lat) && lng < x1 + ((lat - y1) * (x2 - x1)) / (y2 - y1)) inside = !inside;
      if (inside) return p;
    }
    return null;
  };
  return { at, cityOf };
}

// ---------- OSM ----------
const FOOD = /^(restaurant|cafe|bar|pub|fast_food|biergarten|ice_cream)$/;
const STAY = /^(hotel|guest_house|hostel|motel|chalet|apartment|camp_site|caravan_site)$/;
const has = (v, re) => v === undefined || re.test(v); // absent tag = assume dogs welcome

export function osmCategories(t) {
  const name = t.name || '';
  const cats = new Set();
  const vet = t.amenity === 'veterinary' || t.healthcare === 'veterinary' || t.office === 'veterinary';
  if (vet) {
    cats.add('veterinaire');
    if (String(t.opening_hours).trim() === '24/7') cats.add('urgences-24h');
    if (/ost[eé]o/i.test(name)) cats.add('osteopathe');
    if (/kin[eé]|fysio|physio|revalidatie|r[eé][eé]ducation/i.test(name)) cats.add('kine-physio');
    if (/hydro/i.test(name)) cats.add('hydrotherapie');
    if (/dent|tand/i.test(name)) cats.add('dentisterie');
  }
  const groom = t.shop === 'pet_grooming' || t.craft === 'dog_grooming' || (t.shop === 'pet' && (t.grooming === 'yes' || t.pet_grooming === 'yes'));
  if (groom) cats.add(/domicile|mobiel|mobile|aan huis|op verplaatsing|at home/i.test(name + ' ' + (t.description || '')) ? 'toiletteur-domicile' : 'toiletteur-salon');
  if (t.shop === 'pet' && has(t.pet, /dog|cat|food|yes/)) cats.add('animalerie');
  if (t.amenity === 'animal_boarding' && has(t.animal_boarding, /dog|pet/)) {
    cats.add('pension');
    if (/garderie|day ?care|dagopvang|cr[eè]che/i.test(name)) cats.add('garderie');
  }
  if (t.amenity === 'animal_shelter' && has(t.animal_shelter, /dog|pet|mammal/)) cats.add('refuge');
  if (t.amenity === 'animal_training' && has(t.animal_training, /dog/)) {
    cats.add('educateur');
    if (/comportement|gedrag|behaviou?r/i.test(name)) cats.add('comportementaliste');
  }
  if (t.leisure === 'dog_park' && !cats.size) cats.add('parc-a-chiens');
  if (t.dog === 'yes' && (FOOD.test(t.amenity || '') || STAY.test(t.tourism || ''))) cats.add('lieu-dog-friendly');
  return [...cats];
}

function isClosed(t) {
  return t.disused === 'yes' || t.abandoned === 'yes' || /^(closed|off)$/i.test(t.opening_hours || '')
    || Object.keys(t).some(k => /^(disused|abandoned|was|removed|demolished):/.test(k))
    || (t.end_date && t.end_date <= new Date().toISOString().slice(0, 10))
    || /\((ferm[eé]|gesloten|closed)\)/i.test(t.name || '');
}

function osmRecords(json, postal, fetchedAt) {
  const out = [];
  for (const e of json.elements) {
    const t = e.tags || {};
    if (!t.name?.trim() || isClosed(t)) continue;
    const lat = e.lat ?? e.center?.lat, lng = e.lon ?? e.center?.lon;
    if (lat == null) continue;
    const categories = osmCategories(t);
    if (!categories.length) continue;
    const poly = postal.at(lat, lng);
    let pc = /^\d{4}$/.test(t['addr:postcode'] || '') ? t['addr:postcode'] : poly?.code ?? null;
    const isMobile = categories.includes('toiletteur-domicile');
    out.push({
      source: 'osm', source_ref: `${e.type}/${e.id}`, name: t.name.trim(), categories,
      street: t['addr:street'] || null, house_number: t['addr:housenumber'] || null,
      postal_code: pc, city: t['addr:city'] || (pc && (pc === poly?.code ? poly.city : postal.cityOf[pc])) || null,
      region: regionFromPostal(pc), lat: +lat.toFixed(7), lng: +lng.toFixed(7),
      phone: e164(t.phone || t['contact:phone'] || t['contact:mobile'] || t.mobile),
      email: email(t.email || t['contact:email']),
      website: url(t.website || t['contact:website'] || t.url),
      opening_hours: t.opening_hours || null, is_mobile: isMobile,
      bce_number: bceFormat(t['ref:vatin']), regulatory_number: null,
      license: 'ODbL', fetched_at: fetchedAt,
    });
  }
  return out;
}

// ---------- Registry: Vlaanderen erkende dierenasielen ----------
async function fetchCached(name, href) {
  const file = join(RAW, name);
  if (!existsSync(file)) {
    const res = await fetch(href, { headers: { 'User-Agent': UA } });
    if (!res.ok) throw new Error(`${href} -> ${res.status}`);
    writeFileSync(file, await res.text());
  }
  return readFileSync(file, 'utf8');
}

const decode = s => s.replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&#0?39;|&rsquo;/g, "'").replace(/\s+/g, ' ').trim();

async function flandersShelters() {
  const href = 'https://www.vlaanderen.be/natuur-milieu-en-klimaat/dieren/erkende-dierenasielen';
  const html = await fetchCached('vl_asielen.html', href);
  const fetchedAt = statSync(join(RAW, 'vl_asielen.html')).mtime.toISOString();
  const out = [];
  for (const [, row] of html.matchAll(/<tr>([\s\S]*?)<\/tr>/g)) {
    const cells = [...row.matchAll(/<t[hd][^>]*>([\s\S]*?)<\/t[hd]>/g)].map(m => decode(m[1]));
    if (cells.length < 4 || cells[0] === 'Naam' || !/\bhond/i.test(cells[3])) continue;
    const [name, streetFull, gemeente] = cells;
    const m = gemeente.match(/^(\d{4})\s+(.+)$/);
    const sm = streetFull.match(/^(.*?)\s+(\d+\S*(?:\s+bus\s+\S+)?)$/);
    out.push({
      source: 'registry', source_ref: `vl-asiel/${normName(name)}-${m?.[1] ?? 'x'}`,
      name, categories: ['refuge'],
      street: sm ? sm[1] : streetFull || null, house_number: sm ? sm[2] : null,
      postal_code: m?.[1] ?? null, city: m?.[2] ?? (gemeente || null), region: regionFromPostal(m?.[1]),
      lat: null, lng: null, phone: null, email: null, website: null, opening_hours: null, is_mobile: false,
      bce_number: null, regulatory_number: null,
      license: 'Vlaamse overheid - vrij hergebruik (Bestuursdecreet art. II.55), bron: vlaanderen.be', fetched_at: fetchedAt,
    });
  }
  return out;
}

// Nominatim (OSM, ODbL) for the few registry rows with a street address. Policy: max 1 req/s, cached.
async function geocode(records) {
  const file = join(RAW, 'nominatim.json');
  const cache = existsSync(file) ? JSON.parse(readFileSync(file, 'utf8')) : {};
  for (const r of records) {
    if (r.lat != null || !r.street || !r.postal_code) continue;
    const q = `${r.street} ${r.house_number ?? ''}, ${r.postal_code} ${r.city}, Belgium`.replace(/\s+/g, ' ');
    if (!(q in cache)) {
      await new Promise(res => setTimeout(res, 1100));
      const res = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=be&q=${encodeURIComponent(q)}`, { headers: { 'User-Agent': UA } });
      cache[q] = res.ok ? (await res.json())[0] ?? null : null;
      writeFileSync(file, JSON.stringify(cache, null, 1));
    }
    const hit = cache[q];
    if (hit) { r.lat = +(+hit.lat).toFixed(7); r.lng = +(+hit.lon).toFixed(7); }
  }
}

// ---------- merge duplicates ----------
const SOURCE_RANK = { osm: 0, bce: 1, registry: 2 };
export function dedupe(records) {
  const byName = new Map(), out = [];
  const sorted = [...records].sort((a, b) => SOURCE_RANK[a.source] - SOURCE_RANK[b.source]);
  for (const r of sorted) {
    const key = normName(r.name);
    const bucket = byName.get(key) ?? [];
    // Same source: within 50 m. Cross-source (registry/BCE addresses are geocoded or absent): same postal code also counts.
    const twin = bucket.find(o => (o.lat != null && r.lat != null && distanceM(o, r) < 50)
      || ((o.lat == null || r.lat == null || o.source !== r.source) && o.postal_code && o.postal_code === r.postal_code));
    if (twin) {
      twin.categories = [...new Set([...twin.categories, ...r.categories])];
      for (const k of Object.keys(r)) if (twin[k] == null && r[k] != null) twin[k] = r[k];
      twin.is_mobile ||= r.is_mobile;
      continue;
    }
    bucket.push(r); byName.set(key, bucket); out.push(r);
  }
  return out;
}

// ---------- main ----------
if (import.meta.main) {
  const pois = await overpass('osm_pois', POI_QUERY);
  const postal = loadPostal(await overpass('osm_postal_codes', POSTAL_QUERY));
  const osmAt = statSync(join(RAW, 'osm_pois.json')).mtime.toISOString();

  const registry = await flandersShelters();
  await geocode(registry);
  const bceFile = join(ROOT, 'database/data/bce_providers.json');
  const bce = existsSync(bceFile) ? JSON.parse(readFileSync(bceFile, 'utf8')) : [];

  const all = dedupe([...osmRecords(pois, postal, osmAt), ...bce, ...registry])
    .sort((a, b) => (a.source + a.source_ref).localeCompare(b.source + b.source_ref));
  writeFileSync(OUT, JSON.stringify(all, null, 1) + '\n');

  validate(all);
  const count = f => all.reduce((m, r) => (f(r).forEach(k => m[k] = (m[k] || 0) + 1), m), {});
  console.log(`providers.json: ${all.length} records (osm ${pois.elements.length} raw, registry ${registry.length}, bce ${bce.length})`);
  console.table(count(r => r.categories));
  console.table(count(r => [r.region ?? 'null']));
  console.table(count(r => [r.source]));
}
