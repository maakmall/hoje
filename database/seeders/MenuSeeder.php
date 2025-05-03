<?php

namespace Database\Seeders;

use App\Enums\MenuCategory;
use App\Models\Menu;
use App\Models\MenuPrice;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            // Signature Coffee
            [
                'name' => 'Hoje Aren Delight',
                'price' => 25000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Hoje Butter Bliss',
                'price' => 27000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Hoje Popcorn Dream',
                'price' => 27000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Hoje Pandan Delight',
                'price' => 27000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Hoje Matcha Aren Latte',
                'price' => 30000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            // Espresso Based
            [
                'name' => 'Americano',
                'price' => [
                    'hot' => 20000,
                    'cold' => 21000
                ],
                'description' => 'Blend Water and Arabica Coffee',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Cappucino',
                'price' => 27000,
                'description' => 'Fresh Milk with Arabica Coffee',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Latte',
                'price' => [
                    'hot' => 25000,
                    'cold' => 26000
                ],
                'description' => 'Fresh Milk with Arabica Coffee',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Espresso',
                'price' => 25000,
                'description' => 'Arabica Coffee',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Flavour Latte Caramel',
                'price' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Flavour Latte Hazelnut',
                'price' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Flavour Latte Vanilla',
                'price' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            // Sparkling
            [
                'name' => 'Sparkling Kiwi',
                'price' => 26000,
                'description' => 'Flavour Kiwi combined Zoda wth Garnish Kiwi',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Sparkling Orange',
                'price' => 26000,
                'description' => 'Flavour Orange combined Zoda wth Garnish Orange and Lemon',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Sparkling Strawberry',
                'price' => 26000,
                'description' => 'Flavour Strawberry combined Zoda wth Garnish Strawberry',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            // Tea
            [
                'name' => 'Black Tea',
                'price' => [
                    'hot' => 16000,
                    'cold' => 17000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Lemon Tea',
                'price' => [
                    'hot' => 19000,
                    'cold' => 20000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Lychee Tea',
                'price' => 22000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            // Milk Based
            [
                'name' => 'Chocolate',
                'price' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Chocomint',
                'price' => 30000,
                'description' => 'Milky and Chocomint',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Charcoal',
                'price' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Matcha',
                'price' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Red Velvet',
                'price' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Helado Rose',
                'price' => 30000,
                'description' => 'Milky and creamy strawbery',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            // Moctail
            [
                'name' => 'Blue Paradise',
                'price' => 28000,
                'description' => 'Blue citrus with lemon',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            [
                'name' => 'Rosemary',
                'price' => 28000,
                'description' => 'Butterfly tea, strawbery with blueberry',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Beverage,
                'availability' => true,
            ],
            // Rice Bowl
            [
                'name' => 'Chicken Pop Ricebowl',
                'price' => 30000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Chicken Pop Ricebowl Mentai',
                'price' => 33000,
                'description' => 'Steam Rice Combined with Chicken Pop combined Mentai Sauce',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Ayam Suwir Sambal Pedas',
                'price' => 30000,
                'description' => 'Steam Rice with Chicken additional basiland Chilli Sauce',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Ayam Suwir Sambal Matah',
                'price' => 30000,
                'description' => 'Steam Rice with Chicken additional Matah Sauce',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Chicken Katsu',
                'price' => 35000,
                'description' => 'Steam rice with chicken katsu juicy',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Chicken Katsu Mentai',
                'price' => 37000,
                'description' => 'Steam Rice with Combined Chicken Katsu juicy combine Sauce Mentai',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            // Nusantara
            [
                'name' => 'Nasi Goreng',
                'price' => 29000,
                'description' => 'Fried Rice Combined with meatball, egg and Crackers',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Nasi Goreng Hoje',
                'price' => 33000,
                'description' => 'Fried Rice Combined with Seafood, egg and Crackers',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Nasi Ayam Tulang Lunak',
                'price' => 35000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            // Spaghetti
            [
                'name' => 'Bolognise',
                'price' => 35000,
                'description' => 'Pasta with Ground Beef and Light Tomato Sauce',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Aglio Olio',
                'price' => 35000,
                'description' => 'Spicy Pasta with Grilled Chicken',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Carbonara',
                'price' => 37000,
                'description' => 'Pasta with Ground Beef and Light Tomato Sauce',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            // Appetizer
            [
                'name' => 'Chicken Pop',
                'price' => 25000,
                'description' => 'Original Chicken Coated in Flour',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Cireng',
                'price' => 22000,
                'description' => 'Cireng with Rujak Chili Sauce',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'French Fries',
                'price' => 20000,
                'description' => 'Original French Frie with Chili Sauce and Mayonaise',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Mix Platter',
                'price' => 35000,
                'description' => 'French Fries, Nugget and Sausages',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Onion Rings',
                'price' => 27000,
                'description' => 'Crispy Onion Rings with Chili Sauce and Mayonaise',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Pangsit Mini',
                'price' => 30000,
                'description' => 'Original Mini Wonton with Bangkok Chili Sauce',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Pisang Coklat',
                'price' => 25000,
                'description' => 'Crispy Banana with topping Chocolates',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Dimsum',
                'price' => 25000,
                'description' => 'Dimsum Steam with HOJE Sauce',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Chicken Wings',
                'price' => 27000,
                'description' => null,
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Tahu Cabai Garam',
                'price' => 22000,
                'description' => 'Crispy Tofu Combined with Chili Salt',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
            [
                'name' => 'Cheese Roll',
                'price' => 27000,
                'description' => 'The savory taste of cheese blends perfectly',
                'image' => 'images/logo-text.png',
                'category' => MenuCategory::Food,
                'availability' => true,
            ],
        ];

        foreach ($menus as $menuData) {
            $priceData = $menuData['price'];
            unset($menuData['price']);

            $menu = Menu::create($menuData);

            if (is_array($priceData)) {
                foreach ($priceData as $variant => $price) {
                    MenuPrice::create([
                        'menu_id' => $menu->id,
                        'variant_beverage' => $variant,
                        'price' => $price,
                    ]);
                }
            } else {
                MenuPrice::create([
                    'menu_id' => $menu->id,
                    'variant_beverage' => null,
                    'price' => $priceData,
                ]);
            }
        }
    }
}
