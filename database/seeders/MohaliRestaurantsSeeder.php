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
        'pizza'        => 'https://images.unsplash.com/photo-1513104890138-7c749659a591',
        'pizza_alt'    => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002',
        'biryani'      => 'https://images.unsplash.com/photo-1589302168068-964664d93dc0',
        'burger'       => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd',
        'rolls'        => 'https://images.unsplash.com/photo-1601050690117-94f5f6fa8bd7',
        'dessert'      => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb',
        'chinese'      => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624',
        'starter'      => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1',
        'fries'        => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877',
        'chaat'        => 'https://images.unsplash.com/photo-1601050690597-df0568f70950',
        'tandoori'     => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0',
        'curry'        => 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398',
        'curry_alt'    => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641',
        'rice'         => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d',
        'south_indian' => 'https://images.unsplash.com/photo-1630383249896-424e482df921',
        'bread'        => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641',
        'drink'        => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699',
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
                'image' => $this->img('tandoori', 1200),
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
                'image' => $this->img('chaat', 1200),
                'items' => [
                    ['name' => 'Aloo Tikki Chaat', 'category' => 'Starters', 'price' => 70, 'veg' => true, 'img' => 'chaat'],
                    ['name' => 'Pani Puri', 'category' => 'Starters', 'price' => 60, 'veg' => true, 'img' => 'chaat'],
                    ['name' => 'Paneer Kathi Roll', 'category' => 'Rolls', 'price' => 120, 'veg' => true, 'img' => 'rolls'],
                    ['name' => 'Chicken Seekh Roll', 'category' => 'Rolls', 'price' => 140, 'veg' => false, 'img' => 'rolls'],
                    ['name' => 'Samosa Chaat', 'category' => 'Starters', 'price' => 80, 'veg' => true, 'img' => 'chaat'],
                    ['name' => 'Masala Chaas', 'category' => 'Drinks', 'price' => 40, 'veg' => true, 'img' => 'drink'],
                ],
            ],
            [
                'name' => 'Punjab Da Swaad', 'pincode' => '160055',
                'address' => 'Phase 7 Market Road, Shahi Majra, Sector 55, Mohali',
                'cuisines' => 'Punjabi, North Indian',
                'image' => $this->img('rice', 1200),
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
                'image' => $this->img('pizza_alt', 1200),
                'items' => [
                    ['name' => 'Farmhouse Pizza', 'category' => 'Pizza', 'price' => 270, 'veg' => true, 'img' => 'pizza'],
                    ['name' => 'Chicken Supreme Pizza', 'category' => 'Pizza', 'price' => 330, 'veg' => false, 'img' => 'pizza'],
                    ['name' => 'Classic Cheese Burger', 'category' => 'Burger', 'price' => 170, 'veg' => false, 'img' => 'burger'],
                    ['name' => 'Peri Peri Fries', 'category' => 'Starters', 'price' => 110, 'veg' => true, 'img' => 'fries'],
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
            $restaurant->update(['image' => $data['image']]);

            foreach ($data['items'] as $item) {
                $foodItem = FoodItem::firstOrCreate(
                    ['restaurant_id' => $restaurant->id, 'name' => $item['name']],
                    [
                        'category_id'   => $catId($item['category']),
                        'description'   => $item['name'] . ' — a house favorite, freshly prepared to order.',
                        'price'         => $item['price'],
                        'image'         => FoodPhotoCatalog::urlFor($item['name'], $this->img($item['img'])),
                        'is_veg'        => $item['veg'],
                        'is_available'  => true,
                    ]
                );
                $foodItem->update(['image' => FoodPhotoCatalog::urlFor($item['name'], $this->img($item['img']))]);
            }
        }

        $this->command?->info('Seeded ' . count($restaurants) . ' Shahi Majra / Mohali restaurants with menu items.');
    }
}
