<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\FoodItem;

// Demo restaurants for the Shahi Majra locality of S.A.S. Nagar (Mohali),
// which sits right next to Chandigarh. Pincode 160055 covers Shahi Majra
// Phase 7 / Sector 55; 160059 covers the Phase 5 / Sector 58 side of it.
// Run with: php artisan db:seed --class=MohaliRestaurantsSeeder
class MohaliRestaurantsSeeder extends Seeder
{
    protected array $photos = [
        'pizza'    => 'https://images.unsplash.com/photo-1566843972142-a7fcb70de55a',
        'biryani'  => 'https://images.unsplash.com/photo-1542462712-2a516549a9f6',
        'burger'   => 'https://images.unsplash.com/photo-1542574271-7f3b92e6c821',
        'rolls'    => 'https://images.unsplash.com/photo-1515022376298-7333f33e704b',
        'dessert'  => 'https://images.unsplash.com/photo-1593701461250-d7b22dfd3a77',
        'chinese'  => 'https://images.unsplash.com/photo-1673258551460-268b47871a32',
        'starter'  => 'https://images.unsplash.com/photo-1532636875304-0c89119d9b4d',
        'curry'    => 'https://images.unsplash.com/photo-1708782344490-9026aaa5eec7',
        'rice'     => 'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6',
        'bread'    => 'https://images.unsplash.com/photo-1655979284091-eea0e93405ee',
        'drink'    => 'https://images.unsplash.com/photo-1716956755600-4d32af2b8f87',
    ];

    protected function img(string $key, int $size = 500): string
    {
        return $this->photos[$key] . "?fm=jpg&q=70&w={$size}&h={$size}&fit=crop&auto=format";
    }

    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'demo.partner@zomatoclone.test'],
            [
                'name'     => 'Demo Restaurant Partner',
                'password' => Hash::make('password'),
                'role'     => 'restaurant_owner',
            ]
        );

        $categories = Category::pluck('id', 'name');
        $catId = fn (string $name) => $categories[$name] ?? $categories->first();

        $restaurants = [
            [
                'name' => 'Majra Tandoor', 'pincode' => '160055',
                'address' => 'Shop 12, Main Market, Shahi Majra, Phase 7, Sector 55, Mohali',
                'cuisines' => 'North Indian, Tandoori',
                'image' => $this->img('curry', 1200),
                'items' => [
                    ['name' => 'Tandoori Chicken', 'category' => 'Starters', 'price' => 300, 'veg' => false, 'img' => 'starter'],
                    ['name' => 'Butter Chicken', 'category' => 'Curries', 'price' => 290, 'veg' => false, 'img' => 'curry'],
                    ['name' => 'Paneer Lababdar', 'category' => 'Curries', 'price' => 230, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Dal Tadka', 'category' => 'Curries', 'price' => 170, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Tandoori Roti', 'category' => 'Breads', 'price' => 25, 'veg' => true, 'img' => 'bread'],
                    ['name' => 'Butter Naan', 'category' => 'Breads', 'price' => 55, 'veg' => true, 'img' => 'bread'],
                ],
            ],
            [
                'name' => 'Mohali Chaat Corner', 'pincode' => '160055',
                'address' => 'Near Gurudwara Sahib, Shahi Majra, Sector 55, Mohali',
                'cuisines' => 'Street Food, Chaat',
                'image' => $this->img('rolls', 1200),
                'items' => [
                    ['name' => 'Aloo Tikki Chaat', 'category' => 'Starters', 'price' => 70, 'veg' => true, 'img' => 'starter'],
                    ['name' => 'Pani Puri', 'category' => 'Starters', 'price' => 60, 'veg' => true, 'img' => 'starter'],
                    ['name' => 'Paneer Kathi Roll', 'category' => 'Rolls', 'price' => 120, 'veg' => true, 'img' => 'rolls'],
                    ['name' => 'Chicken Seekh Roll', 'category' => 'Rolls', 'price' => 140, 'veg' => false, 'img' => 'rolls'],
                    ['name' => 'Samosa Chaat', 'category' => 'Starters', 'price' => 80, 'veg' => true, 'img' => 'starter'],
                    ['name' => 'Masala Chaas', 'category' => 'Drinks', 'price' => 40, 'veg' => true, 'img' => 'drink'],
                ],
            ],
            [
                'name' => 'Punjab Da Swaad', 'pincode' => '160055',
                'address' => 'Phase 7 Market Road, Shahi Majra, Sector 55, Mohali',
                'cuisines' => 'Punjabi, North Indian',
                'image' => $this->img('curry', 1200),
                'items' => [
                    ['name' => 'Sarson Da Saag & Makki Roti', 'category' => 'Curries', 'price' => 220, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Amritsari Chicken Curry', 'category' => 'Curries', 'price' => 280, 'veg' => false, 'img' => 'curry'],
                    ['name' => 'Rajma Chawal', 'category' => 'Rice', 'price' => 160, 'veg' => true, 'img' => 'rice'],
                    ['name' => 'Chole Bhature', 'category' => 'Curries', 'price' => 150, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Amritsari Fish Fry', 'category' => 'Starters', 'price' => 260, 'veg' => false, 'img' => 'starter'],
                    ['name' => 'Lassi', 'category' => 'Drinks', 'price' => 70, 'veg' => true, 'img' => 'drink'],
                ],
            ],
            [
                'name' => 'Cafe Shahi Bites', 'pincode' => '160055',
                'address' => 'Shahi Majra Chowk, Phase 5, Sector 58, Mohali',
                'cuisines' => 'Cafe, Fast Food, Pizza',
                'image' => $this->img('pizza', 1200),
                'items' => [
                    ['name' => 'Farmhouse Pizza', 'category' => 'Pizza', 'price' => 270, 'veg' => true, 'img' => 'pizza'],
                    ['name' => 'Chicken Supreme Pizza', 'category' => 'Pizza', 'price' => 330, 'veg' => false, 'img' => 'pizza'],
                    ['name' => 'Classic Cheese Burger', 'category' => 'Burger', 'price' => 170, 'veg' => false, 'img' => 'burger'],
                    ['name' => 'Peri Peri Fries', 'category' => 'Starters', 'price' => 110, 'veg' => true, 'img' => 'starter'],
                    ['name' => 'Chocolate Brownie', 'category' => 'Desserts', 'price' => 110, 'veg' => true, 'img' => 'dessert'],
                    ['name' => 'Cold Coffee', 'category' => 'Drinks', 'price' => 100, 'veg' => true, 'img' => 'drink'],
                ],
            ],
        ];

        foreach ($restaurants as $data) {
            $restaurant = Restaurant::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'user_id'      => $owner->id,
                    'name'         => $data['name'],
                    'address'      => $data['address'],
                    'pincode'      => $data['pincode'],
                    'cuisines'     => $data['cuisines'],
                    'image'        => $data['image'],
                    'is_approved'  => true,
                    'is_open'      => true,
                ]
            );

            foreach ($data['items'] as $item) {
                FoodItem::firstOrCreate(
                    ['restaurant_id' => $restaurant->id, 'name' => $item['name']],
                    [
                        'category_id'   => $catId($item['category']),
                        'description'   => $item['name'] . ' — a house favorite, freshly prepared to order.',
                        'price'         => $item['price'],
                        'image'         => $this->img($item['img']),
                        'is_veg'        => $item['veg'],
                        'is_available'  => true,
                    ]
                );
            }
        }

        $this->command?->info('Seeded ' . count($restaurants) . ' Shahi Majra / Mohali restaurants with menu items.');
    }
}
