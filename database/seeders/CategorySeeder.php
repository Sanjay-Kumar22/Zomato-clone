<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // `icon` stores a food photo URL used as the category tile background.
        $q = 'fm=jpg&q=70&w=200&h=200&fit=crop&auto=format';

        $categories = [
            'Pizza' => 'photo-1513104890138-7c749659a591',
            'Biryani' => 'photo-1589302168068-964664d93dc0',
            'Burger' => 'photo-1568901346375-23c9450c58cd',
            'Rolls' => 'photo-1601050690117-94f5f6fa8bd7',
            'Desserts' => 'photo-1563805042-7684c019e1cb',
            'Chinese' => 'photo-1569718212165-3a8278d5f624',
            'Starters' => 'photo-1555939594-58d7cb561ad1',
            'Curries' => 'photo-1603894584373-5ac82b2ae398',
            'Rice' => 'photo-1546833999-b9f581a1996d',
            'Breads' => 'photo-1565557623262-b51c2513a641',
            'Drinks' => 'photo-1572490122747-3968b75cc699',
        ];

        foreach ($categories as $name => $photo) {
            $values = [
                'icon' => "https://images.unsplash.com/{$photo}?{$q}",
                'updated_at' => now(),
            ];

            if (DB::table('categories')->where('name', $name)->exists()) {
                DB::table('categories')->where('name', $name)->update($values);
            } else {
                DB::table('categories')->insert($values + [
                    'name' => $name,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
