// Belgian municipalities (communes / gemeenten, OSM admin_level=8, ODbL) and the postal localities they contain.
//   node scripts/data/communes.mjs   -> database/data/communes.json (cached Overpass answer in database/data/raw/)
// City pages are built per municipality; postal localities (sections such as Glain or Klemskerke) point to theirs.
import { readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { overpass, ROOT } from './overpass.mjs';

const AREA = 'area["ISO3166-1"="BE"][admin_level=2]->.be;';
export const COMMUNES_QUERY = `[out:json][timeout:600];${AREA}
rel[boundary=administrative][admin_level=8](area.be);
out geom;`;
const OUT = join(ROOT, 'database/data/communes.json');
const LOCALES = ['fr', 'nl', 'en', 'de'];

// GeoNames writes every segment with a capital ("Vaux-Sur-Sûre", "Braine-L'Alleud", "Beveren-Aan-Den-Ijzer").
const SMALL = new Set(['sur', 'sous', 'lez', 'lès', 'la', 'le', 'les', 'de', 'du', 'des', 'aux', 'en', 'et', 'devant', 'aan', 'op', 'bij', 'ter', 'den', 'in', 'het', 'over']);
export function placeCase(name) {
  if (!name) return name;
  return name
    .split('-')
    .map((part, i) => {
      if (i > 0 && SMALL.has(part.toLowerCase())) return part.toLowerCase();
      return part.replace(/^([LD])'/, (m, l) => (i > 0 ? l.toLowerCase() + "'" : m));
    })
    .join('-')
    .replace(/^'S /, "'s ")
    .replace(/\bIj/g, 'IJ');
}

/** Polygon index over the municipality boundaries (ray casting on every ring segment). */
export function communeIndex(json) {
  const communes = json.elements.map((rel) => {
    const segs = [];
    for (const m of rel.members) if (m.type === 'way' && m.geometry)
      for (let i = 1; i < m.geometry.length; i++) segs.push([m.geometry[i - 1].lon, m.geometry[i - 1].lat, m.geometry[i].lon, m.geometry[i].lat]);
    const centre = rel.members.find((m) => m.role === 'admin_centre' && m.type === 'node');
    return { rel, segs, b: rel.bounds, centre };
  });
  const at = (lat, lng) => {
    for (const c of communes) {
      if (lat < c.b.minlat || lat > c.b.maxlat || lng < c.b.minlon || lng > c.b.maxlon) continue;
      let inside = false;
      for (const [x1, y1, x2, y2] of c.segs)
        if ((y1 > lat) !== (y2 > lat) && lng < x1 + ((lat - y1) * (x2 - x1)) / (y2 - y1)) inside = !inside;
      if (inside) return c;
    }
    return null;
  };
  return { communes, at };
}

const refOf = (rel) => rel.tags['ref:INS'] || rel.tags['ref:nis'] || `osm-${rel.id}`;

if (import.meta.main) {
  const json = await overpass('osm_communes', COMMUNES_QUERY);
  const { communes, at } = communeIndex(json);

  // GeoNames localities (code, name, lat, lng) -> municipality containing their point.
  const localities = {};
  for (const line of readFileSync(join(ROOT, 'database/data/geonames-postal-codes-be.tsv'), 'utf8').split('\n')) {
    const cols = line.split('\t');
    if (cols.length < 11) continue;
    const [, code, name, , , , , , , lat, lng] = cols;
    const commune = at(+lat, +lng);
    if (commune) (localities[refOf(commune.rel)] ??= []).push(`${code}|${placeCase(name)}`);
  }

  const out = communes
    .filter(({ rel }) => rel.tags.name)
    .map(({ rel, b, centre }) => {
      const t = rel.tags;
      const local = t['name:nl'] && !t['name:fr'] ? t['name:nl'] : t.name.split(' - ')[0];
      const fallback = (lang) => t[`name:${lang}`] || local;
      const names = Object.fromEntries(LOCALES.map((l) => [l, fallback(l)]));
      // English and German: an official exonym when OSM has one, otherwise the local name.
      return {
        ref: refOf(rel),
        names,
        lat: +(centre?.lat ?? (b.minlat + b.maxlat) / 2).toFixed(5),
        lng: +(centre?.lon ?? (b.minlon + b.maxlon) / 2).toFixed(5),
        population: Number.parseInt(String(t.population || '').replace(/\D/g, ''), 10) || null,
        localities: localities[refOf(rel)] ?? [],
      };
    })
    .sort((a, c) => a.ref.localeCompare(c.ref));

  writeFileSync(OUT, JSON.stringify(out, null, 1) + '\n');
  console.log(`communes.json: ${out.length} municipalities, ${Object.values(localities).flat().length} localities mapped`);
}
