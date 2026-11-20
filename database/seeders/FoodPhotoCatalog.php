<?php

namespace Database\Seeders;

use InvalidArgumentException;

class FoodPhotoCatalog
{
    private const PHOTOS = [
        'Butter Chicken' => 'photo-1603894584373-5ac82b2ae398',
        'Paneer Tikka Masala' => 'photo-1666001120694-3ebe8fd207be',
        'Paneer Lababdar' => 'photo-1567188040759-fb8a883dc6d8',
        'Dal Makhani' => 'photo-1789983665266-f1a6ea13b311',
        'Dal Tadka' => 'photo-1755090154817-58d9d36ec988',
        'Chicken Tikka' => 'photo-1599487488170-d11ec9c172f0',
        'Garlic Naan' => 'photo-1697155406014-04dc649b0953',
        'Butter Naan' => 'photo-1633030318854-b076ff72770f',
        'Mutton Rogan Josh' => 'photo-1708782344137-21c48d98dfcc',
        'Veg Manchurian' => 'photo-1673258551460-268b47871a32',
        'Chicken Fried Rice' => 'photo-1603133872878-684f208fb84b',
        'Chilli Chicken' => 'photo-1710508774177-7ac2f3492675',
        'Veg Spring Rolls' => 'photo-1768701544400-dfa8ca509d10',
        'Hakka Noodles' => 'photo-1569718212165-3a8278d5f624',
        'Schezwan Paneer' => 'photo-1631452180519-c014fe946bc7',
        'Hyderabadi Chicken Biryani' => 'photo-1589302168068-964664d93dc0',
        'Mutton Biryani' => 'photo-1691171047462-66025ecd5efc',
        'Veg Biryani' => 'photo-1542462712-2a516549a9f6',
        'Egg Biryani' => 'photo-1599043513900-ed6fe01d3833',
        'Boondi Raita' => 'photo-1668236534990-73c4ed23043c',
        'Chicken 65' => 'photo-1603496987351-f84a3ba5ec85',
        'Margherita Pizza' => 'photo-1513104890138-7c749659a591',
        'Farmhouse Pizza' => 'photo-1574071318508-1cdbab80d002',
        'Chicken Tikka Pizza' => 'photo-1566843972142-a7fcb70de55a',
        'Chicken Supreme Pizza' => 'photo-1776810250102-7459c4e0edc9',
        'Pepperoni Pizza' => 'photo-1604382354936-07c5d9983bd3',
        'Garlic Bread' => 'photo-1509440159596-0249088772ff',
        'Pasta Alfredo' => 'photo-1473093295043-cdd812d0e601',
        'Classic Veg Burger' => 'photo-1550547660-d9450f859349',
        'Chicken Burger' => 'photo-1568901346375-23c9450c58cd',
        'Double Cheese Burger' => 'photo-1757834787931-ddd70f3379a5',
        'Crispy Veg Burger' => 'photo-1766735126781-1b2527043bf0',
        'French Fries' => 'photo-1630384060421-cb20d0e0649d',
        'Cold Coffee' => 'photo-1461023058943-07fcbe16d735',
        'Masala Dosa' => 'photo-1668236543090-82eba5ee5976',
        'Idli Sambar' => 'photo-1632104667384-06f58cb7ad44',
        'Chicken Chettinad' => 'photo-1690401767645-595de0e0e5f8',
        'Curd Rice' => 'photo-1546833999-b9f581a1996d',
        'Uttapam' => 'photo-1694849789325-914b71ab4075',
        'Filter Coffee' => 'photo-1758221054731-744af4bf4d68',
        'Gulab Jamun' => 'photo-1666190092159-3171cf0fbb12',
        'Rasmalai' => 'photo-1758910536889-43ce7b3199fd',
        'Chocolate Brownie' => 'photo-1636743715220-d8f8dd900b87',
        'Kaju Katli' => 'photo-1782033032439-a2c656ca2f59',
        'Ice Cream Sundae' => 'photo-1560008581-09826d1de69e',
        'Mango Lassi' => 'photo-1692620609860-be6717812f71',
        'Chicken Curry' => 'photo-1565557623262-b51c2513a641',
        'Palak Paneer' => 'photo-1631452180539-96aca7d48617',
        'Fish Curry' => 'photo-1765265432611-17d3f2da2d5d',
        'Chana Masala' => 'photo-1788602564560-9bcbf0f9acb6',
        'Tandoori Roti' => 'photo-1586444248902-2f64eddc13df',
        'Jeera Rice' => 'photo-1681546898018-961e2a05c6fa',
        'Chicken Kathi Roll' => 'photo-1626700051175-6818013e1d4f',
        'Paneer Roll' => 'photo-1601050690117-94f5f6fa8bd7',
        'Egg Roll' => 'photo-1606525437679-037aca74a3e9',
        'Veg Frankie' => 'photo-1646530208887-8a791bff4701',
        'Aloo Tikki Roll' => 'photo-1601050690597-df0568f70950',
        'Nimbu Pani' => 'photo-1544145945-f90425340c7e',
        'Tandoori Chicken' => 'photo-1705359573325-f2006d5e459f',
        'Aloo Tikki Chaat' => 'photo-1788620473431-df599434a3d3',
        'Pani Puri' => 'photo-1781243680771-1fea4521e196',
        'Paneer Kathi Roll' => 'photo-1601050690117-94f5f6fa8bd7',
        'Chicken Seekh Roll' => 'photo-1708782341231-426344114ace',
        'Samosa Chaat' => 'photo-1788602563638-840d0d1d3ad2',
        'Masala Chaas' => 'photo-1544145945-f90425340c7e',
        'Sarson Da Saag & Makki Roti' => 'photo-1631452180539-96aca7d48617',
        'Amritsari Chicken Curry' => 'photo-1596797038530-2c107229654b',
        'Rajma Chawal' => 'photo-1680993032090-1ef7ea9b51e5',
        'Chole Bhature' => 'photo-1626132647523-66f5bf380027',
        'Amritsari Fish Fry' => 'photo-1519708227418-c8fd9a32b7a2',
        'Lassi' => 'photo-1692620609860-be6717812f71',
        'Classic Cheese Burger' => 'photo-1561758033-d89a9ad46330',
        'Peri Peri Fries' => 'photo-1768215610440-fcb9631c7ee2',
    ];

    public static function urlFor(string $dish, ?string $fallback = null, int $size = 500): string
    {
        $photo = self::PHOTOS[$dish] ?? null;

        if (!$photo) {
            if ($fallback !== null) {
                return $fallback;
            }

            throw new InvalidArgumentException("No food photo is configured for [{$dish}].");
        }

        return "https://images.unsplash.com/{$photo}?auto=format&fit=crop&w={$size}&h={$size}&q=80";
    }
}
