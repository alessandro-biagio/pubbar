<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;

class UserController extends Controller
{
    public function orders(User $user)
    {
        $orders = Order::where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(20);

        return view('staff.users.orders', compact('user','orders'));
    }
}
