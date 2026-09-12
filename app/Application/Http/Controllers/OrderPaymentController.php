<?php

namespace App\Application\Http\Controllers;

use App\Domain\Orders\Actions\TransitionOrderStateAction;
use App\Domain\Orders\Exceptions\InvalidOrderTransitionException;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\States\PaidState;
use App\Domain\Orders\States\ShippedState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate; 
use App\Domain\Orders\States\CancelledState;
use App\Domain\Orders\States\DeliveredState;

class OrderPaymentController extends Controller
{
    public function pay(Order $order, TransitionOrderStateAction $action): RedirectResponse
    {
       Gate::authorize('pay', $order);
        try {
            $action->execute($order, PaidState::class, 'Pago simulado desde el dashboard');
        } catch (InvalidOrderTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Pago registrado. Generando factura en segundo plano...');
    }

    public function ship(Order $order, TransitionOrderStateAction $action): RedirectResponse
    {
        Gate::authorize('ship', $order);
        try {
            $action->execute($order, ShippedState::class, 'Envío simulado desde el dashboard');
        } catch (InvalidOrderTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Orden marcada como enviada.');
    }
        public function cancel(Order $order, TransitionOrderStateAction $action): RedirectResponse
    {
        Gate::authorize('cancel', $order);

        try {
            $action->execute($order, CancelledState::class, 'Cancelado por el cliente');
        } catch (InvalidOrderTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Orden cancelada y stock liberado.');
    }
        public function deliver(Order $order, TransitionOrderStateAction $action): RedirectResponse
    {
        Gate::authorize('deliver', $order);

        try {
            $action->execute($order, DeliveredState::class, 'Entrega confirmada');
        } catch (InvalidOrderTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Orden marcada como entregada.');
    }
}