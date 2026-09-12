<?php

namespace App\Domain\Orders\Actions;

use App\Domain\Inventory\Actions\ConsumeInventoryAction;
use App\Domain\Inventory\Actions\ReleaseInventoryAction;
use App\Domain\Orders\Events\OrderStatusChanged;
use App\Domain\Orders\Exceptions\InvalidOrderTransitionException;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\States\OrderState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransitionOrderStateAction
{
    public function __construct(
        private ConsumeInventoryAction $consumeInventoryAction,
        private ReleaseInventoryAction $releaseInventoryAction,
    ) {}

    /**
     * @param class-string<OrderState> $targetStateClass
     */
    public function execute(Order $order, string $targetStateClass, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $targetStateClass, $reason) {

            $order->refresh();
            $currentState = $order->state();

            if (! $currentState->canTransitionTo($targetStateClass)) {
                throw InvalidOrderTransitionException::make(
                    $currentState->label(),
                    (new $targetStateClass($order))->label()
                );
            }

            $fromStatus = $order->status;
            $toStatus = $targetStateClass::key();

            $order->status = $toStatus;

            match ($toStatus) {
                'paid' => $order->paid_at = now(),
                'shipped' => $order->shipped_at = now(),
                'cancelled' => $order->cancelled_at = now(),
                default => null,
            };

            $order->save();

       
            match ($toStatus) {
                'shipped' => $this->consumeInventoryAction->execute($order->id),
                'cancelled' => $this->releaseInventoryAction->execute($order->id),
                default => null,
            };

            $order->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by_user_id' => Auth::id(),
                'reason' => $reason,
                'created_at' => now(),
            ]);

            event(new OrderStatusChanged($order, $fromStatus, $toStatus));

            return $order;
        });
    }
}