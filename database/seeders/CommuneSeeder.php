<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\PostalCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Belgian municipalities from database/data/communes.json (built by scripts/data/communes.mjs from
 * OpenStreetMap, ODbL) and the commune of every postal locality. Idempotent (upsert on the INS code).
 * Run after PostalCodeSeeder.
 */
class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode((string) file_get_contents(database_path('data/communes.json')), true);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $commune = Commune::updateOrCreate(['ref' => $row['ref']], [
                    'names' => $row['names'],
                    'slugs' => Commune::slugsFor($row['names']),
                    'lat' => $row['lat'],
                    'lng' => $row['lng'],
                    'population' => $row['population'],
                ]);

                foreach ($row['localities'] as $locality) {
                    [$code, $name] = explode('|', $locality, 2);
                    PostalCode::query()->where('code', $code)->where('name', $name)->update(['commune_id' => $commune->id]);
                }
            }
        });

        PostalCode::forgetPlaceIndex();
    }
}
