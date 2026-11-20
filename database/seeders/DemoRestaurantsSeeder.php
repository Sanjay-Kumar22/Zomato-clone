<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\FoodItem;

class DemoRestaurantsSeeder extends Seeder
{
    // Free-to-use (Unsplash License) food photos, reused sensibly by dish type.
    protected array $photos = [
        'pizza'        => 'https://images.unsplash.com/photo-1513104890138-7c749659a591',
        'pizza_alt'    => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002',
        'biryani'      => 'https://images.unsplash.com/photo-1589302168068-964664d93dc0',
        'burger'       => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd',
        'rolls'        => 'https://images.unsplash.com/photo-1601050690117-94f5f6fa8bd7',
        'dessert'      => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb',
        'chinese'      => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624',
        'fried_rice'   => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b',
        'starter'      => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1',
        'fries'        => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877',
        'pasta'        => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601',
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
        // Single demo owner account for all seeded restaurants — keeps this
        // seeder idempotent and independent of whichever owner accounts
        // already exist in your database.
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
                'name' => 'Spice Villa', 'pincode' => '160036',
                'address' => 'SCO 45, Sector 22, Chandigarh',
                'cuisines' => 'North Indian, Mughlai',
                'image' => $this->img('curry', 1200),
                'items' => [
                    ['name' => 'Butter Chicken', 'category' => 'Curries', 'price' => 280, 'veg' => false, 'img' => 'curry'],
                    ['name' => 'Paneer Tikka Masala', 'category' => 'Curries', 'price' => 240, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Dal Makhani', 'category' => 'Curries', 'price' => 180, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Chicken Tikka', 'category' => 'Starters', 'price' => 260, 'veg' => false, 'img' => 'starter'],
                    ['name' => 'Garlic Naan', 'category' => 'Breads', 'price' => 60, 'veg' => true, 'img' => 'bread'],
                    ['name' => 'Mutton Rogan Josh', 'category' => 'Curries', 'price' => 320, 'veg' => false, 'img' => 'curry'],
                ],
            ],
            [
                'name' => 'Dragon Wok', 'pincode' => '160017',
                'address' => 'Elante Mall Road, Industrial Area, Chandigarh',
                'cuisines' => 'Chinese, Asian',
                'image' => $this->img('chinese', 1200),
                'items' => [
                    ['name' => 'Veg Manchurian', 'category' => 'Chinese', 'price' => 180, 'veg' => true, 'img' => 'chinese'],
                    ['name' => 'Chicken Fried Rice', 'category' => 'Rice', 'price' => 220, 'veg' => false, 'img' => 'fried_rice'],
                    ['name' => 'Chilli Chicken', 'category' => 'Starters', 'price' => 260, 'veg' => false, 'img' => 'starter'],
                    ['name' => 'Veg Spring Rolls', 'category' => 'Rolls', 'price' => 150, 'veg' => true, 'img' => 'rolls'],
                    ['name' => 'Hakka Noodles', 'category' => 'Chinese', 'price' => 190, 'veg' => true, 'img' => 'chinese'],
                    ['name' => 'Schezwan Paneer', 'category' => 'Chinese', 'price' => 210, 'veg' => true, 'img' => 'chinese'],
                ],
            ],
            [
                'name' => 'Biryani Junction', 'pincode' => '160055',
                'address' => 'Zirakpur Highway, Near Bus Stand, Chandigarh',
                'cuisines' => 'Biryani, Hyderabadi',
                'image' => $this->img('biryani', 1200),
                'items' => [
                    ['name' => 'Hyderabadi Chicken Biryani', 'category' => 'Biryani', 'price' => 260, 'veg' => false, 'img' => 'biryani'],
                    ['name' => 'Mutton Biryani', 'category' => 'Biryani', 'price' => 320, 'veg' => false, 'img' => 'biryani'],
                    ['name' => 'Veg Biryani', 'category' => 'Biryani', 'price' => 200, 'veg' => true, 'img' => 'biryani'],
                    ['name' => 'Egg Biryani', 'category' => 'Biryani', 'price' => 220, 'veg' => false, 'img' => 'biryani'],
                    ['name' => 'Boondi Raita', 'category' => 'Curries', 'price' => 40, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Chicken 65', 'category' => 'Starters', 'price' => 240, 'veg' => false, 'img' => 'starter'],
                ],
            ],
            [
                'name' => 'Pizza Mania', 'pincode' => '160022',
                'address' => 'Sector 35 Market, Chandigarh',
                'cuisines' => 'Pizza, Italian',
                'image' => $this->img('pizza', 1200),
                'items' => [
                    ['name' => 'Margherita Pizza', 'category' => 'Pizza', 'price' => 220, 'veg' => true, 'img' => 'pizza'],
                    ['name' => 'Farmhouse Pizza', 'category' => 'Pizza', 'price' => 280, 'veg' => true, 'img' => 'pizza'],
                    ['name' => 'Chicken Tikka Pizza', 'category' => 'Pizza', 'price' => 320, 'veg' => false, 'img' => 'pizza'],
                    ['name' => 'Pepperoni Pizza', 'category' => 'Pizza', 'price' => 340, 'veg' => false, 'img' => 'pizza'],
                    ['name' => 'Garlic Bread', 'category' => 'Starters', 'price' => 120, 'veg' => true, 'img' => 'bread'],
                    ['name' => 'Pasta Alfredo', 'category' => 'Starters', 'price' => 200, 'veg' => true, 'img' => 'pasta'],
                ],
            ],
            [
                'name' => 'Burger Barn', 'pincode' => '160014',
                'address' => 'Sector 17 Plaza, Chandigarh',
                'cuisines' => 'Fast Food, American',
                'image' => $this->img('burger', 1200),
                'items' => [
                    ['name' => 'Classic Veg Burger', 'category' => 'Burger', 'price' => 120, 'veg' => true, 'img' => 'burger'],
                    ['name' => 'Chicken Burger', 'category' => 'Burger', 'price' => 160, 'veg' => false, 'img' => 'burger'],
                    ['name' => 'Double Cheese Burger', 'category' => 'Burger', 'price' => 200, 'veg' => false, 'img' => 'burger'],
                    ['name' => 'Crispy Veg Burger', 'category' => 'Burger', 'price' => 140, 'veg' => true, 'img' => 'burger'],
                    ['name' => 'French Fries', 'category' => 'Starters', 'price' => 100, 'veg' => true, 'img' => 'fries'],
                    ['name' => 'Cold Coffee', 'category' => 'Drinks', 'price' => 90, 'veg' => true, 'img' => 'drink'],
                ],
            ],
            [
                'name' => 'South Spice', 'pincode' => '160009',
                'address' => 'Sector 8 Market, Chandigarh',
                'cuisines' => 'South Indian',
                'image' => $this->img('south_indian', 1200),
                'items' => [
                    ['name' => 'Masala Dosa', 'category' => 'Rice', 'price' => 120, 'veg' => true, 'img' => 'south_indian'],
                    ['name' => 'Idli Sambar', 'category' => 'Rice', 'price' => 90, 'veg' => true, 'img' => 'south_indian'],
                    ['name' => 'Chicken Chettinad', 'category' => 'Curries', 'price' => 260, 'veg' => false, 'img' => 'curry'],
                    ['name' => 'Curd Rice', 'category' => 'Rice', 'price' => 100, 'veg' => true, 'img' => 'rice'],
                    ['name' => 'Uttapam', 'category' => 'Rice', 'price' => 110, 'veg' => true, 'img' => 'south_indian'],
                    ['name' => 'Filter Coffee', 'category' => 'Drinks', 'price' => 50, 'veg' => true, 'img' => 'drink'],
                ],
            ],
            [
                'name' => 'Sweet Treats', 'pincode' => '160030',
                'address' => 'Sector 9 Market, Chandigarh',
                'cuisines' => 'Desserts, Bakery',
                'image' => $this->img('dessert', 1200),
                'items' => [
                    ['name' => 'Gulab Jamun', 'category' => 'Desserts', 'price' => 80, 'veg' => true, 'img' => 'dessert'],
                    ['name' => 'Rasmalai', 'category' => 'Desserts', 'price' => 100, 'veg' => true, 'img' => 'dessert'],
                    ['name' => 'Chocolate Brownie', 'category' => 'Desserts', 'price' => 120, 'veg' => true, 'img' => 'dessert'],
                    ['name' => 'Kaju Katli', 'category' => 'Desserts', 'price' => 150, 'veg' => true, 'img' => 'dessert'],
                    ['name' => 'Ice Cream Sundae', 'category' => 'Desserts', 'price' => 130, 'veg' => true, 'img' => 'dessert'],
                    ['name' => 'Mango Lassi', 'category' => 'Drinks', 'price' => 90, 'veg' => true, 'img' => 'drink'],
                ],
            ],
            [
                'name' => 'Curry House', 'pincode' => '160045',
                'address' => 'Sector 40 Market, Chandigarh',
                'cuisines' => 'Curries, North Indian',
                'image' => $this->img('curry_alt', 1200),
                'items' => [
                    ['name' => 'Chicken Curry', 'category' => 'Curries', 'price' => 240, 'veg' => false, 'img' => 'curry'],
                    ['name' => 'Palak Paneer', 'category' => 'Curries', 'price' => 200, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Fish Curry', 'category' => 'Curries', 'price' => 280, 'veg' => false, 'img' => 'curry'],
                    ['name' => 'Chana Masala', 'category' => 'Curries', 'price' => 160, 'veg' => true, 'img' => 'curry'],
                    ['name' => 'Tandoori Roti', 'category' => 'Breads', 'price' => 30, 'veg' => true, 'img' => 'bread'],
                    ['name' => 'Jeera Rice', 'category' => 'Rice', 'price' => 110, 'veg' => true, 'img' => 'rice'],
                ],
            ],
            [
                'name' => 'Roll Junction', 'pincode' => '160011',
                'address' => 'Sector 15 Market, Chandigarh',
                'cuisines' => 'Rolls, Street Food',
                'image' => $this->img('rolls', 1200),
                'items' => [
                    ['name' => 'Chicken Kathi Roll', 'category' => 'Rolls', 'price' => 130, 'veg' => false, 'img' => 'rolls'],
                    ['name' => 'Paneer Roll', 'category' => 'Rolls', 'price' => 110, 'veg' => true, 'img' => 'rolls'],
                    ['name' => 'Egg Roll', 'category' => 'Rolls', 'price' => 100, 'veg' => false, 'img' => 'rolls'],
                    ['name' => 'Veg Frankie', 'category' => 'Rolls', 'price' => 90, 'veg' => true, 'img' => 'rolls'],
                    ['name' => 'Aloo Tikki Roll', 'category' => 'Rolls', 'price' => 80, 'veg' => true, 'img' => 'rolls'],
                    ['name' => 'Nimbu Pani', 'category' => 'Drinks', 'price' => 40, 'veg' => true, 'img' => 'drink'],
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

        $this->command?->info('Seeded ' . count($restaurants) . ' demo restaurants with menu items.');
    }
}
