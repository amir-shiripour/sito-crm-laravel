<?php

namespace Modules\Market\App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class CartController extends Controller
{
    /**
     * Display the cart page.
     * The actual cart logic is handled by the Livewire component.
     */
    public function index()
    {
        return view('market::web.cart.index');
    }
}
