<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class DemoBannersSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Biryani nights, sorted',
                'subtitle' => 'Slow-cooked favourites from kitchens near you.',
                'image' => 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?auto=format&fit=crop&w=1400&h=560&q=85',
                'url' => route('home', ['search' => 'Biryani'], false),
                'sort_order' => 1,
            ],
            [
                'title' => 'Make it a pizza night',
                'subtitle' => 'Hot, cheesy favourites for a seriously good evening.',
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=1400&h=560&q=85',
                'url' => route('home', ['search' => 'Pizza'], false),
                'sort_order' => 2,
            ],
            [
                'title' => 'Big burger energy',
                'subtitle' => 'Stacked classics and crispy sides, delivered fresh.',
                'image' => 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?auto=format&fit=crop&w=1400&h=560&q=85',
                'url' => route('home', ['search' => 'Burger'], false),
                'sort_order' => 3,
            ],
        ];

        foreach ($banners as $banner) {
            $record = Banner::firstOrCreate(
                ['title' => $banner['title']],
                [...$banner, 'is_active' => true],
            );

            $destinationHost = parse_url((string) $record->url, PHP_URL_HOST);
            $applicationHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if ($destinationHost && $destinationHost === $applicationHost) {
                $record->update(['url' => $banner['url']]);
            }
        }
    }
}
