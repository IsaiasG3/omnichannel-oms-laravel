<?php

namespace Database\Seeders;

use App\Domain\Inventory\Models\Product;
use App\Domain\Orders\Actions\CreateOrderAction;
use App\Domain\Orders\Actions\TransitionOrderStateAction;
use App\Domain\Orders\States\CancelledState;
use App\Domain\Orders\States\DeliveredState;
use App\Domain\Orders\States\PaidState;
use App\Domain\Orders\States\ShippedState;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@test.com')->first();
        $products = Product::orderBy('id')->get();

        if (! $user || $products->isEmpty()) {
            return;
        }

        /** @var CreateOrderAction $createOrder */
        $createOrder = app(CreateOrderAction::class);
        /** @var TransitionOrderStateAction $transition */
        $transition = app(TransitionOrderStateAction::class);

       
        $createOrder->execute($user, [
            ['product_id' => $products[0]->id, 'quantity' => 1],
        ]);

    
        $order2 = $createOrder->execute($user, [
            ['product_id' => $products[0]->id, 'quantity' => 2],
        ]);
        $transition->execute($order2, PaidState::class, 'Pago demo');

      
        $order3 = $createOrder->execute($user, [
            ['product_id' => $products[1]->id, 'quantity' => 1],
        ]);
        $transition->execute($order3, PaidState::class, 'Pago demo');
        $transition->execute($order3, ShippedState::class, 'Envío demo');

     
        $order4 = $createOrder->execute($user, [
            ['product_id' => $products[1]->id, 'quantity' => 1],
        ]);
        $transition->execute($order4, PaidState::class, 'Pago demo');
        $transition->execute($order4, ShippedState::class, 'Envío demo');
        $transition->execute($order4, DeliveredState::class, 'Entrega demo');


        $order5 = $createOrder->execute($user, [
            ['product_id' => $products[2]->id, 'quantity' => 1],
        ]);
        $transition->execute($order5, CancelledState::class, 'Cliente se arrepintió');
    }
}