<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'nama' => 'Admin',
            'email' => 'admin@hoje.com',
        ]);

        Location::insert([
            [
                'nama' => 'Indoor Non Smooking',
                'kapasitas' => 20,
            ],
            [
                'nama' => 'Indoor Smooking',
                'kapasitas' => 80,
            ],
            [
                'nama' => 'Outdoor',
                'kapasitas' => 40,
            ],
        ]);

        $locations = Location::all();
        $tables = [];
        $tableNumber = 1;

        foreach ($locations as $location) {
            $tableCount = ceil($location->kapasitas / 4);

            for ($i = 0; $i < $tableCount; $i++) {
                $tables[] = [
                    'nomor' => $tableNumber++,
                    'id_lokasi' => $location->id,
                ];
            }
        }

        Table::insert($tables);

        $this->call(MenuSeeder::class);
    }
}
