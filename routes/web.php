    <?php

    use App\Http\Controllers\ProfileController;
    use Illuminate\Foundation\Application;
    use Illuminate\Support\Facades\Route;
    use App\Application\Http\Controllers\OrderDashboardController;
    use App\Application\Http\Controllers\PurchaseController;
    use App\Domain\Inventory\Models\InventoryLocation;
    use App\Application\Http\Controllers\InventoryController;
    use App\Application\Http\Controllers\CheckoutController;
    use App\Application\Http\Controllers\OrderPaymentController;
    use App\Application\Http\Controllers\InvoiceController;
    use App\Application\Http\Controllers\DashboardController;
    use Inertia\Inertia;

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/purchase-attempt', [PurchaseController::class, 'attempt'])->middleware('throttle:purchase-attempts')->name('purchase.attempt');
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    });
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/orders', [OrderDashboardController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderDashboardController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/deliver', [OrderPaymentController::class, 'deliver'])->name('orders.deliver');
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->middleware('throttle:checkout')
            ->name('checkout.store');

        Route::post('/orders/{order}/pay', [OrderPaymentController::class, 'pay'])->name('orders.pay');
        Route::post('/orders/{order}/ship', [OrderPaymentController::class, 'ship'])->name('orders.ship');
        Route::post('/orders/{order}/cancel', [OrderPaymentController::class, 'cancel'])->name('orders.cancel');
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    });
    Route::get('/inventory-locations/{inventoryLocation}/available', function (InventoryLocation $inventoryLocation) {
        return response()->json(['available' => $inventoryLocation->fresh()->availableQuantity()]);
    })->middleware('auth');
    require __DIR__.'/auth.php';
