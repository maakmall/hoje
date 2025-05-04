<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $menus = Menu::available()->with('prices')->get();

        return view('menu', [
            'title' => 'Menu',
            'foods' => $menus->where('category', 'food')->all(),
            'beverages' => $menus->where('category', 'beverage')->all(),
        ]);
    }

    public function checkout(): View
    {
        return view('checkout', [
            'title' => 'Checkout',
        ]);
    }
}
