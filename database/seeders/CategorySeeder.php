<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // `icon` now stores a real food photo URL (free-to-use, Unsplash License)
        // instead of an emoji or a filename — rendered as a small circular thumbnail.
        $q = 'fm=jpg&q=70&w=200&h=200&fit=crop&auto=format';

        Category::insert([
            ['name' => 'Pizza',    'icon' => "https://images.unsplash.com/photo-1566843972142-a7fcb70de55a?{$q}"],
            ['name' => 'Biryani',  'icon' => "https://images.unsplash.com/photo-1542462712-2a516549a9f6?{$q}"],
            ['name' => 'Burger',   'icon' => "https://images.unsplash.com/photo-1542574271-7f3b92e6c821?{$q}"],
            ['name' => 'Rolls',    'icon' => "https://images.unsplash.com/photo-1515022376298-7333f33e704b?{$q}"],
            ['name' => 'Desserts', 'icon' => "https://images.unsplash.com/photo-1593701461250-d7b22dfd3a77?{$q}"],
            ['name' => 'Chinese',  'icon' => "https://images.unsplash.com/photo-1673258551460-268b47871a32?{$q}"],
            ['name' => 'Starters', 'icon' => "https://images.unsplash.com/photo-1532636875304-0c89119d9b4d?{$q}"],
            ['name' => 'Curries',  'icon' => "https://images.unsplash.com/photo-1708782344490-9026aaa5eec7?{$q}"],
            ['name' => 'Rice',     'icon' => "https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?{$q}"],
            ['name' => 'Breads',   'icon' => "https://images.unsplash.com/photo-1655979284091-eea0e93405ee?{$q}"],
            ['name' => 'Drinks',   'icon' => "https://images.unsplash.com/photo-1716956755600-4d32af2b8f87?{$q}"],
        ]);
    }
}
