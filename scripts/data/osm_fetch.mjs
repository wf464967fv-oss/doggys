// Fetch dog-related POIs + postal code boundaries for Belgium from OpenStreetMap (Overpass, ODbL).
// Run: node scripts/data/osm_fetch.mjs   (cached in database/data/raw/, delete files to refresh)
import { overpass } from './overpass.mjs';

const AREA = 'area["ISO3166-1"="BE"][admin_level=2]->.be;';

export const POI_QUERY = `[out:json][timeout:300];${AREA}
(
  nwr[amenity=veterinary](area.be);
  nwr[healthcare=veterinary](area.be);
  nwr[shop=pet](area.be);
  nwr[shop=pet_grooming](area.be);
  nwr[craft=dog_grooming](area.be);
  nwr[amenity=animal_boarding](area.be);
  nwr[amenity=animal_shelter](area.be);
  nwr[amenity=animal_training](area.be);
  nwr[leisure=dog_park](area.be);
  nwr[office~"^(veterinary|animal_.*|pet.*)$"](area.be);
  nwr[craft~"^(animal_.*|pet.*|dog.*)$"](area.be);
  nwr[healthcare~"^(animal_.*)$"](area.be);
  nwr[dog=yes][amenity~"^(restaurant|cafe|bar|pub|fast_food|biergarten|ice_cream)$"](area.be);
  nwr[dog=yes][tourism~"^(hotel|guest_house|hostel|motel|chalet|apartment|camp_site|caravan_site)$"](area.be);
);
out center tags;`;

export const POSTAL_QUERY = `[out:json][timeout:300];${AREA}
rel[boundary=postal_code](area.be);
out geom;`;

if (import.meta.main) {
  const pois = await overpass('osm_pois', POI_QUERY);
  console.log('POIs:', pois.elements.length);
  const pc = await overpass('osm_postal_codes', POSTAL_QUERY);
  console.log('Postal boundaries:', pc.elements.length);
}
