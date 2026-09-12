<?php

namespace App\Policies;

use App\Domain\Orders\Models\Order;
use App\Models\User;

class OrderPolicy
{
  
    public function view(User $user, Order $order): bool
    {
        return $user->isWarehouseStaff() || $user->id === $order->user_id;
    }

  
    public function pay(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }


    public function ship(User $user, Order $order): bool
    {
        return $user->isWarehouseStaff();
    }

 
    public function deliver(User $user, Order $order): bool
    {
        return $user->isWarehouseStaff();
    }

   
    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->isWarehouseStaff();
    }
}