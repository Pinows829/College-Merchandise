<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('home');
Route::get('/colleges/{college:code}', [StoreController::class, 'college'])->name('colleges.show');
Route::get('/products/{product}', [StoreController::class, 'product'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AccountController::class, 'loginForm'])->name('login');
    Route::post('/login', [AccountController::class, 'login'])->name('login.store');
    Route::get('/register', [AccountController::class, 'registerForm'])->name('register');
    Route::post('/register', [AccountController::class, 'register'])->name('register.store');
});
Route::post('/logout', [AccountController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/items/{sizeId}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [CheckoutController::class, 'form'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders.index');
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/products', [AdminController::class, 'products'])->name('products.index');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::get('/courses', [AdminController::class, 'courses'])->name('courses.index');
    Route::post('/courses', [AdminController::class, 'storeCourse'])->name('courses.store');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [AdminController::class, 'showOrder'])->name('orders.show');
    Route::patch('/orders/{order}', [AdminController::class, 'updateOrder'])->name('orders.update');
});
