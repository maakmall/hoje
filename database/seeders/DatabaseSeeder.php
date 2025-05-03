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
            'name' => 'Admin',
            'email' => 'admin@hoje.com',
        ]);

        Location::insert([
            [
                'name' => 'Indoor Non Smooking',
                'capacity' => 20,
            ],
            [
                'name' => 'Indoor Smooking',
                'capacity' => 80,
            ],
            [
                'name' => 'Outdoor',
                'capacity' => 40,
            ],
        ]);

        $locations = Location::all();
        $tables = [];
        $tableNumber = 1;

        foreach ($locations as $location) {
            $tableCount = ceil($location->capacity / 4);

            for ($i = 0; $i < $tableCount; $i++) {
                $tables[] = [
                    'number' => $tableNumber++,
                    'location_id' => $location->id,
                ];
            }
        }

        Table::insert($tables);

        $this->call(MenuSeeder::class);
    }
}
