<?php

namespace Database\Seeders;

use App\Models\PostalCode;
use Illuminate\Database\Seeder;

/**
 * Belgian postal codes with coordinates from GeoNames (CC BY 4.0), see database/data/SOURCES.txt.
 * Idempotent: upserts on (code, name).
 */
class PostalCodeSeeder extends Seeder
{
    private const REGIONS = ['BRU' => 'brussels', 'VLG' => 'flanders', 'WAL' => 'wallonia'];

    /**
     * Official names of the main cities in each site language (GeoNames only has the local name).
     *
     * @var array<string, array<string, string>>
     */
    private const TRANSLATIONS = [
        'Bruxelles' => ['fr' => 'Bruxelles', 'nl' => 'Brussel', 'en' => 'Brussels', 'de' => 'Brüssel'],
        'Antwerpen' => ['fr' => 'Anvers', 'nl' => 'Antwerpen', 'en' => 'Antwerp', 'de' => 'Antwerpen'],
        'Gent' => ['fr' => 'Gand', 'nl' => 'Gent', 'en' => 'Ghent', 'de' => 'Gent'],
        'Liège' => ['fr' => 'Liège', 'nl' => 'Luik', 'en' => 'Liège', 'de' => 'Lüttich'],
        'Brugge' => ['fr' => 'Bruges', 'nl' => 'Brugge', 'en' => 'Bruges', 'de' => 'Brügge'],
        'Namur' => ['fr' => 'Namur', 'nl' => 'Namen', 'en' => 'Namur', 'de' => 'Namur'],
        'Mons' => ['fr' => 'Mons', 'nl' => 'Bergen', 'en' => 'Mons', 'de' => 'Bergen'],
        'Leuven' => ['fr' => 'Louvain', 'nl' => 'Leuven', 'en' => 'Leuven', 'de' => 'Löwen'],
        'Mechelen' => ['fr' => 'Malines', 'nl' => 'Mechelen', 'en' => 'Mechelen', 'de' => 'Mecheln'],
        'Kortrijk' => ['fr' => 'Courtrai', 'nl' => 'Kortrijk', 'en' => 'Kortrijk', 'de' => 'Kortrijk'],
        'Oostende' => ['fr' => 'Ostende', 'nl' => 'Oostende', 'en' => 'Ostend', 'de' => 'Ostende'],
        'Tournai' => ['fr' => 'Tournai', 'nl' => 'Doornik', 'en' => 'Tournai', 'de' => 'Doornik'],
        'Arlon' => ['fr' => 'Arlon', 'nl' => 'Aarlen', 'en' => 'Arlon', 'de' => 'Arel'],
        'Aalst' => ['fr' => 'Alost', 'nl' => 'Aalst', 'en' => 'Aalst', 'de' => 'Aalst'],
        'Sint-Niklaas' => ['fr' => 'Saint-Nicolas', 'nl' => 'Sint-Niklaas', 'en' => 'Sint-Niklaas', 'de' => 'Sint-Niklaas'],
        'Wavre' => ['fr' => 'Wavre', 'nl' => 'Waver', 'en' => 'Wavre', 'de' => 'Wavre'],
    ];

    public function run(): void
    {
        $handle = fopen(database_path('data/geonames-postal-codes-be.tsv'), 'r');
        $now = now();
        $rows = [];

        while (($line = fgetcsv($handle, separator: "\t", escape: '')) !== false) {
            [, $code, $name, , $regionCode, $province, , , , $lat, $lng] = $line;

            $rows[] = [
                'code' => $code,
                'name' => $name,
                'name_translations' => isset(self::TRANSLATIONS[$name]) ? json_encode(self::TRANSLATIONS[$name]) : null,
                'lat' => $lat,
                'lng' => $lng,
                'province' => $regionCode === 'BRU' ? null : $province,
                'region' => self::REGIONS[$regionCode],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        fclose($handle);

        foreach (array_chunk($rows, 500) as $chunk) {
            PostalCode::upsert($chunk, ['code', 'name'], ['name_translations', 'lat', 'lng', 'province', 'region', 'updated_at']);
        }
    }
}
