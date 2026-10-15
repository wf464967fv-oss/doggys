// Shared Overpass client: one request at a time, retries across public endpoints,
// raw responses cached in database/data/raw/ (gitignored). Delete the cache file to refetch.
import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';

export const ROOT = new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
export const RAW = join(ROOT, 'database/data/raw');
const UA = 'Doggys data import (contact@doggys.be)';
const ENDPOINTS = (process.env.OVERPASS_URL ? [process.env.OVERPASS_URL] : []).concat([
  'https://overpass-api.de/api/interpreter',
  'https://overpass.private.coffee/api/interpreter',
  'https://overpass.kumi.systems/api/interpreter',
]);

export async function overpass(name, query) {
  mkdirSync(RAW, { recursive: true });
  const file = join(RAW, `${name}.json`);
  if (existsSync(file)) return JSON.parse(readFileSync(file, 'utf8'));
  for (let attempt = 0; attempt < 12; attempt++) {
    const url = ENDPOINTS[attempt % ENDPOINTS.length];
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'User-Agent': UA, 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'data=' + encodeURIComponent(query),
        signal: AbortSignal.timeout(400_000),
      });
      const text = await res.text();
      if (res.ok && text.startsWith('{')) {
        const json = JSON.parse(text);
        if (json.remark && /error|timed out/i.test(json.remark)) throw new Error(json.remark);
        writeFileSync(file, text);
        return json;
      }
      console.warn(`[overpass] ${url} -> HTTP ${res.status}`);
    } catch (e) {
      console.warn(`[overpass] ${url} -> ${e.message}`);
    }
    await new Promise(r => setTimeout(r, 10_000 * Math.min(attempt + 1, 4)));
  }
  throw new Error(`Overpass failed for ${name}`);
}
