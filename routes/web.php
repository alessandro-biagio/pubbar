<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentPaypalController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

// Staff controllers
use App\Http\Controllers\Staff\OrderController as StaffOrderController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\KitchenCapacityController;
use App\Http\Controllers\Staff\ProductController as StaffProductController;
use App\Http\Controllers\Staff\ProductVariantController;
use App\Http\Controllers\Staff\CategoryController as StaffCategoryController;
use App\Http\Controllers\Staff\UserManagementController;
use App\Http\Controllers\Staff\UserController as StaffUserController;
use App\Http\Controllers\OrderHistoryController;


/*
|--------------------------------------------------------------------------
| Area STAFF (solo is_staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        // Dashboard principale staff
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

        // Gestione ordini staff
        Route::get('/orders', [StaffOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [StaffOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [StaffOrderController::class, 'updateStatus'])->name('orders.updateStatus');

        // Gestione prodotti (CRUD)
        Route::resource('products', StaffProductController::class)->except(['show']);
        Route::patch('/products/{product}/toggle', [StaffProductController::class, 'toggle'])->name('products.toggle');

        // Varianti prodotto
        Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
        Route::patch('/products/{product}/variants/{variant}/toggle', [ProductVariantController::class, 'toggle'])->name('products.variants.toggle');

        // Gestione categorie
        Route::resource('categories', StaffCategoryController::class)->except(['show']);

        // Capacità cucina
        Route::get('/capacity', [KitchenCapacityController::class, 'edit'])->name('capacity.edit');
        Route::post('/capacity/defaults', [KitchenCapacityController::class, 'updateDefaults'])->name('capacity.updateDefaults');
        Route::post('/capacity/overrides', [KitchenCapacityController::class, 'saveOverrides'])->name('capacity.saveOverrides');

        // ===== Gestione utenti =====
        // Visibile a tutti gli staff
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');

        // Azioni riservate ai superuser
        Route::middleware('superuser')->group(function () {
            Route::patch('/users/{user}/toggle-staff', [UserManagementController::class, 'toggleStaff'])->name('users.toggleStaff');
            Route::patch('/users/{user}/toggle-superuser', [UserManagementController::class, 'toggleSuperuser'])->name('users.toggleSuperuser');
        });

        // Storico ordini utente (visibile agli staff)
        Route::get('/users/{user}/orders', [StaffUserController::class, 'orders'])->name('users.orders');

        // AJAX: popup dettagli ordine
        Route::get('/orders/{order}/modal', [StaffOrderController::class, 'modal'])
            ->name('orders.modal');
    });


/*
|--------------------------------------------------------------------------
| Flusso checkout e pagamenti (utenti loggati)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');

    // Pagamento con PayPal
    Route::get('/payment/{order}', [PaymentPaypalController::class, 'show'])->name('payment.show');
    Route::post('/paypal/create-order/{order}', [PaymentPaypalController::class, 'createOrder'])->name('paypal.create');
    Route::post('/paypal/capture-order', [PaymentPaypalController::class, 'captureOrder'])->name('paypal.capture');

    // Pagamento fallito
    Route::get('/payment/failed/{order}', function (\App\Models\Order $order) {
        return view('payment_failed', [
            'order' => $order,
            'fail_message' => session('fail_message'),
        ]);
    })->name('payment.failed');
});

/*
|--------------------------------------------------------------------------
| Catalogo pubblico
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/c/{slug}', [CatalogController::class, 'category'])->name('category.show');
Route::get('/p/{slug}', [CatalogController::class, 'product'])->name('product.show');

/*
|--------------------------------------------------------------------------
| Carrello
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');

/*
|--------------------------------------------------------------------------
| Dashboard (SOLO staff)
| Non-staff: 403. Gli staff vengono reindirizzati alla dashboard staff.
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return redirect()->route('staff.dashboard');
})->middleware(['auth', 'verified', 'staff'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Profilo utente
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/my/orders', [OrderHistoryController::class, 'index'])->name('orders.my');

    Route::post('/my/orders/{order}/revert', [OrderHistoryController::class, 'revertToCart'])
        ->name('orders.revert');

    Route::post('/my/orders/{order}/revert-beacon', [OrderHistoryController::class, 'revertToCartBeacon'])
        ->middleware('signed')
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('orders.revert_beacon');
        
});

require __DIR__ . '/auth.php';
