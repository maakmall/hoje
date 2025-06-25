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
                'nama' => 'Hoje Aren Delight',
                'harga' => 25000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Hoje Butter Bliss',
                'harga' => 27000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Hoje Popcorn Dream',
                'harga' => 27000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Hoje Pandan Delight',
                'harga' => 27000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Hoje Matcha Aren Latte',
                'harga' => 30000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            // Espresso Based
            [
                'nama' => 'Americano',
                'harga' => [
                    'hot' => 20000,
                    'cold' => 21000
                ],
                'deskripsi' => 'Blend Water and Arabica Coffee',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Cappucino',
                'harga' => 27000,
                'deskripsi' => 'Fresh Milk with Arabica Coffee',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Latte',
                'harga' => [
                    'hot' => 25000,
                    'cold' => 26000
                ],
                'deskripsi' => 'Fresh Milk with Arabica Coffee',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Espresso',
                'harga' => 25000,
                'deskripsi' => 'Arabica Coffee',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Flavour Latte Caramel',
                'harga' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Flavour Latte Hazelnut',
                'harga' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Flavour Latte Vanilla',
                'harga' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            // Sparkling
            [
                'nama' => 'Sparkling Kiwi',
                'harga' => 26000,
                'deskripsi' => 'Flavour Kiwi combined Zoda wth Garnish Kiwi',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Sparkling Orange',
                'harga' => 26000,
                'deskripsi' => 'Flavour Orange combined Zoda wth Garnish Orange and Lemon',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Sparkling Strawberry',
                'harga' => 26000,
                'deskripsi' => 'Flavour Strawberry combined Zoda wth Garnish Strawberry',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            // Tea
            [
                'nama' => 'Black Tea',
                'harga' => [
                    'hot' => 16000,
                    'cold' => 17000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Lemon Tea',
                'harga' => [
                    'hot' => 19000,
                    'cold' => 20000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Lychee Tea',
                'harga' => 22000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            // Milk Based
            [
                'nama' => 'Chocolate',
                'harga' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Chocomint',
                'harga' => 30000,
                'deskripsi' => 'Milky and Chocomint',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Charcoal',
                'harga' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Matcha',
                'harga' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Red Velvet',
                'harga' => [
                    'hot' => 26000,
                    'cold' => 27000
                ],
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Helado Rose',
                'harga' => 30000,
                'deskripsi' => 'Milky and creamy strawbery',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            // Moctail
            [
                'nama' => 'Blue Paradise',
                'harga' => 28000,
                'deskripsi' => 'Blue citrus with lemon',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            [
                'nama' => 'Rosemary',
                'harga' => 28000,
                'deskripsi' => 'Butterfly tea, strawbery with blueberry',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Beverage,
                'tersedia' => true,
            ],
            // Rice Bowl
            [
                'nama' => 'Chicken Pop Ricebowl',
                'harga' => 30000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Chicken Pop Ricebowl Mentai',
                'harga' => 33000,
                'deskripsi' => 'Steam Rice Combined with Chicken Pop combined Mentai Sauce',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Ayam Suwir Sambal Pedas',
                'harga' => 30000,
                'deskripsi' => 'Steam Rice with Chicken additional basiland Chilli Sauce',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Ayam Suwir Sambal Matah',
                'harga' => 30000,
                'deskripsi' => 'Steam Rice with Chicken additional Matah Sauce',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Chicken Katsu',
                'harga' => 35000,
                'deskripsi' => 'Steam rice with chicken katsu juicy',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Chicken Katsu Mentai',
                'harga' => 37000,
                'deskripsi' => 'Steam Rice with Combined Chicken Katsu juicy combine Sauce Mentai',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            // Nusantara
            [
                'nama' => 'Nasi Goreng',
                'harga' => 29000,
                'deskripsi' => 'Fried Rice Combined with meatball, egg and Crackers',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Nasi Goreng Hoje',
                'harga' => 33000,
                'deskripsi' => 'Fried Rice Combined with Seafood, egg and Crackers',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Nasi Ayam Tulang Lunak',
                'harga' => 35000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            // Spaghetti
            [
                'nama' => 'Bolognise',
                'harga' => 35000,
                'deskripsi' => 'Pasta with Ground Beef and Light Tomato Sauce',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Aglio Olio',
                'harga' => 35000,
                'deskripsi' => 'Spicy Pasta with Grilled Chicken',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Carbonara',
                'harga' => 37000,
                'deskripsi' => 'Pasta with Ground Beef and Light Tomato Sauce',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            // Appetizer
            [
                'nama' => 'Chicken Pop',
                'harga' => 25000,
                'deskripsi' => 'Original Chicken Coated in Flour',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Cireng',
                'harga' => 22000,
                'deskripsi' => 'Cireng with Rujak Chili Sauce',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'French Fries',
                'harga' => 20000,
                'deskripsi' => 'Original French Frie with Chili Sauce and Mayonaise',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Mix Platter',
                'harga' => 35000,
                'deskripsi' => 'French Fries, Nugget and Sausages',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Onion Rings',
                'harga' => 27000,
                'deskripsi' => 'Crispy Onion Rings with Chili Sauce and Mayonaise',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Pangsit Mini',
                'harga' => 30000,
                'deskripsi' => 'Original Mini Wonton with Bangkok Chili Sauce',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Pisang Coklat',
                'harga' => 25000,
                'deskripsi' => 'Crispy Banana with topping Chocolates',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Dimsum',
                'harga' => 25000,
                'deskripsi' => 'Dimsum Steam with HOJE Sauce',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Chicken Wings',
                'harga' => 27000,
                'deskripsi' => null,
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Tahu Cabai Garam',
                'harga' => 22000,
                'deskripsi' => 'Crispy Tofu Combined with Chili Salt',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
            [
                'nama' => 'Cheese Roll',
                'harga' => 27000,
                'deskripsi' => 'The savory taste of cheese blends perfectly',
                'gambar' => 'images/logo-text.png',
                'kategori' => MenuCategory::Food,
                'tersedia' => true,
            ],
        ];

        foreach ($menus as $menuData) {
            $priceData = $menuData['harga'];
            unset($menuData['harga']);

            $menu = Menu::create($menuData);

            if (is_array($priceData)) {
                foreach ($priceData as $variant => $price) {
                    MenuPrice::create([
                        'id_menu' => $menu->id,
                        'variasi_minuman' => $variant,
                        'harga' => $price,
                    ]);
                }
            } else {
                MenuPrice::create([
                    'id_menu' => $menu->id,
                    'variasi_minuman' => null,
                    'harga' => $priceData,
                ]);
            }
        }
    }
}
