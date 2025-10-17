<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartPageController extends Controller
{
    /**
     * Display the cart page.
     */
    public function index(Request $request)
    {
        $cart = session()->get('cart', []);

        // Get cart items with full equipment details
        $cartItems = [];
        $totalItems = 0;

        foreach ($cart as $equipmentId => $item) {
            $equipment = \App\Models\Equipment::find($equipmentId);

            if ($equipment) {
                $cartItems[] = [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'category' => $equipment->category,
                    'image' => $equipment->image,
                    'available_quantity' => $equipment->quantity,
                    'quantity' => $item['quantity'],
                ];

                $totalItems += $item['quantity'];
            }
        }

        return Inertia::render('Student/Cart', [
            'cartItems' => $cartItems,
            'totalItems' => $totalItems,
        ]);
    }
}
