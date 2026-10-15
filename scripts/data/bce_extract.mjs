// Extract dog-related businesses from the official KBO/BCE Open Data "Full" dump.
//
//   node scripts/data/bce_extract.mjs path/to/KboOpenData_XXXX_YYYY_MM_Full.zip   (or an already-extracted folder)
//   node scripts/data/build.mjs                                                   (merges the result into providers.json)
//
// Procedure (owner, once):
//   1. https://kbopub.economie.fgov.be/kbo-open-data/signup?form -> create the free account (name, e-mail, purpose:
//      "annuaire Doggys"), accept the reuse licence, confirm the e-mail.
//   2. Log in at https://kbopub.economie.fgov.be/kbo-open-data/ -> "Files" -> download the latest
//      KboOpenData_NNNN_YYYY_MM_Full.zip (~300 MB zipped, monthly; *_Update.zip are deltas, not used here).
//   3. Run the two commands above. Info: https://economie.fgov.be/fr/themes/entreprises/banque-carrefour-des/services-pour-tous/donnees-publiques-disponibles/kbo-open-data
// Files used: enterprise.csv, establishment.csv, activity.csv, address.csv, denomination.csv, contact.csv.
// Natural persons (TypeOfEnterprise=1) are kept only when they publish a commercial name (never the person's name).
// Output: database/data/bce_providers.json (same schema as providers.json, lat/lng null: no coordinates in KBO).
//
// NACE codes checked against Statbel's NACEBEL2008_2025_CONVERSIE.xlsx (activity.csv stores them without dots):
const NACE = {
  // NACE-BEL 2008          NACE-BEL 2025
  '75000': ['veterinaire'],                     // 75.000 Activités vétérinaires (both versions)
  '96093': ['toiletteur-salon'], '96993': ['toiletteur-salon'], // Services de soins pour animaux de compagnie (toilettage)
  '96094': ['educateur'],        '96994': ['educateur'],        // Activités de dressage pour animaux de compagnie
  '96095': ['pension'],          '96995': ['pension'],          // Hébergement d'animaux de compagnie
  '47762': ['animalerie'],                      // Commerce de détail d'animaux de compagnie, aliments, accessoires (both)
};
// ponytail: 01.490/01.480 "Élevage d'autres animaux" is skipped: not dog-specific and BCE does not prove the HK agreement.
// Veterinary code 75000 also covers farm/equine vets; kept as 'veterinaire' (the app can filter later).

import { createReadStream, existsSync, mkdtempSync, statSync, writeFileSync } from 'node:fs';
import { createInterface } from 'node:readline';
import { execFileSync } from 'node:child_process';
import { join } from 'node:path';
import { tmpdir } from 'node:os';
import { bceFormat, e164, email, regionFromPostal, url } from './build.mjs';

const ROOT = new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');

export function parseCsvLine(line) {
  const out = []; let cur = '', q = false;
  for (let i = 0; i < line.length; i++) {
    const c = line[i];
    if (q) { if (c === '"') { if (line[i + 1] === '"') { cur += '"'; i++; } else q = false; } else cur += c; }
    else if (c === '"') q = true;
    else if (c === ',') { out.push(cur); cur = ''; }
    else cur += c;
  }
  out.push(cur);
  return out;
}

async function eachRow(dir, file, fn) {
  const rl = createInterface({ input: createReadStream(join(dir, file), 'utf8'), crlfDelay: Infinity });
  let header;
  for await (const line of rl) {
    const cells = parseCsvLine(line.replace(/^﻿/, ''));
    if (!header) { header = cells; continue; }
    fn(Object.fromEntries(header.map((h, i) => [h, cells[i] ?? ''])));
  }
}

export async function extract(dir) {
  const cats = new Map();                  // entity number -> Set(categories)
  await eachRow(dir, 'activity.csv', r => {
    if (r.Classification === 'ANCI' || !['2008', '2025'].includes(r.NaceVersion)) return;
    const c = NACE[r.NaceCode]; if (!c) return;
    const s = cats.get(r.EntityNumber) ?? new Set(); c.forEach(x => s.add(x)); cats.set(r.EntityNumber, s);
  });

  const enterprises = new Map();           // active enterprises only (open data only lists active entities, but be safe)
  await eachRow(dir, 'enterprise.csv', r => { if (r.Status === 'AC') enterprises.set(r.EnterpriseNumber, r.TypeOfEnterprise); });

  const estabOf = new Map();               // enterprise -> [establishments]
  await eachRow(dir, 'establishment.csv', r => {
    const l = estabOf.get(r.EnterpriseNumber) ?? []; l.push(r.EstablishmentNumber); estabOf.set(r.EnterpriseNumber, l);
  });

  // Units: every establishment with a relevant activity; an enterprise with relevant activity but no such
  // establishment is published at its single establishment (if exactly one) or its registered address.
  const units = new Map();                 // unitNumber -> { enterprise, cats }
  const parentOf = new Map([...estabOf].flatMap(([e, list]) => list.map(x => [x, e])));
  for (const [entity, set] of cats) {
    if (parentOf.has(entity)) { const ent = parentOf.get(entity); if (enterprises.has(ent)) units.set(entity, { enterprise: ent, cats: set }); }
  }
  for (const [entity, set] of cats) {
    if (!enterprises.has(entity)) continue;
    const ests = estabOf.get(entity) ?? [];
    const covered = ests.filter(x => units.has(x));
    if (covered.length) { covered.forEach(x => set.forEach(c => units.get(x).cats.add(c))); continue; }
    units.set(ests.length === 1 ? ests[0] : entity, { enterprise: entity, cats: set });
  }
  const wanted = new Set([...units.keys(), ...[...units.values()].map(u => u.enterprise)]);

  const names = new Map();                 // entity -> { '001': name, '003': name } (prefer FR, then NL, then any)
  const langRank = { '1': 0, '2': 1, '3': 2, '4': 3 }; // KBO languages: 1=FR 2=NL 3=DE 4=EN 0=unknown
  await eachRow(dir, 'denomination.csv', r => {
    if (!wanted.has(r.EntityNumber)) return;
    const m = names.get(r.EntityNumber) ?? {}; const prev = m[r.TypeOfDenomination];
    if (!prev || (langRank[r.Language] ?? 9) < (langRank[prev.lang] ?? 9)) m[r.TypeOfDenomination] = { name: r.Denomination, lang: r.Language };
    names.set(r.EntityNumber, m);
  });

  const addresses = new Map();
  await eachRow(dir, 'address.csv', r => {
    if (!wanted.has(r.EntityNumber) || r.DateStrikingOff || !['BAET', 'REGO'].includes(r.TypeOfAddress)) return;
    if (r.CountryFR || r.CountryNL) return; // empty country = Belgium
    addresses.set(r.EntityNumber, r);
  });

  const contacts = new Map();
  await eachRow(dir, 'contact.csv', r => {
    if (!wanted.has(r.EntityNumber)) return;
    const m = contacts.get(r.EntityNumber) ?? {}; m[r.ContactType] ??= r.Value; contacts.set(r.EntityNumber, m);
  });

  const fetchedAt = new Date().toISOString();
  const out = [];
  for (const [unit, { enterprise, cats: set }] of units) {
    const n = names.get(unit) ?? {}, en = names.get(enterprise) ?? {};
    // Natural persons (TypeOfEnterprise 1): only a commercial name is published, never the person's name.
    const name = n['003']?.name || en['003']?.name || (enterprises.get(enterprise) === '2' ? (en['001']?.name || en['002']?.name) : null);
    const a = addresses.get(unit) ?? addresses.get(enterprise);
    if (!name || !a) continue;
    const c = { ...(contacts.get(enterprise) ?? {}), ...(contacts.get(unit) ?? {}) };
    out.push({
      source: 'bce', source_ref: unit, name: name.trim(), categories: [...set],
      street: (a.StreetFR || a.StreetNL || '').trim() || null,
      house_number: [a.HouseNumber, a.Box && `bte ${a.Box}`].filter(Boolean).join(' ') || null,
      postal_code: /^\d{4}$/.test(a.Zipcode) ? a.Zipcode : null,
      city: (a.MunicipalityFR || a.MunicipalityNL || '').trim() || null, region: regionFromPostal(a.Zipcode),
      lat: null, lng: null, phone: e164(c.TEL), email: email(c.EMAIL), website: url(c.WEB),
      opening_hours: null, is_mobile: false, bce_number: bceFormat(enterprise), regulatory_number: null,
      license: 'KBO/BCE Open Data (SPF Economie, réutilisation libre)', fetched_at: fetchedAt,
    });
  }
  return out;
}

if (import.meta.main) {
  let src = process.argv[2];
  if (!src || !existsSync(src)) { console.error('Usage: node scripts/data/bce_extract.mjs <KboOpenData_Full.zip | folder>'); process.exit(1); }
  if (statSync(src).isFile()) { // Windows 10+, macOS and Linux tar (bsdtar) all unpack zip files
    const dir = mkdtempSync(join(tmpdir(), 'kbo-'));
    execFileSync('tar', ['-xf', src, '-C', dir], { stdio: 'inherit' });
    src = dir;
  }
  const rows = await extract(src);
  writeFileSync(join(ROOT, 'database/data/bce_providers.json'), JSON.stringify(rows, null, 1) + '\n');
  console.log(`bce_providers.json: ${rows.length} records. Now run: node scripts/data/build.mjs`);
}
