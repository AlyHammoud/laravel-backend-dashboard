<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoriesController;
use App\Http\Controllers\Api\V1\ItemsController;
use App\Http\Controllers\Api\V1\ProductsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Resources\V1\UserResource;

use App\Http\Controllers\Api\V1\VerifyEmailController;

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    //
    // Routes for Users and Authentication
    //
    Route::put('/update/user/{user}', [AuthController::class, 'update']);
    Route::delete('/delete/{user}', [AuthController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout'])->withoutMiddleware('verified');
    Route::get('/all-users', [AuthController::class, 'getAllUsers']);
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });
    //
    //End of Routes for Users and Authentication
    //

    //
    // Routes for Categories
    //
    Route::post('/category', [CategoriesController::class, 'store']);
    Route::put('category/{category}', [CategoriesController::class, 'update']);
    Route::delete('category/{category}', [CategoriesController::class, 'destroy']);
    //
    //

    //
    // Items
    //
    Route::post('/item', [ItemsController::class, 'store']);
    Route::put('/item/{item}', [ItemsController::class, 'update']);
    Route::delete('/item/{item}', [ItemsController::class, 'destroy']);
    Route::delete('deleteAllItems', [ItemsController::class, 'deleteAllItems']); //all categories and relations
    Route::get('items', [ItemsController::class, 'index'])->name('item.allItems'); // for admin with all translations and all infos
    Route::get('filtered-items/{categoryId?}', [ItemsController::class, 'filteredItems'])->name('item.allFilteredItems'); // for admin with all translations and all infos

    //
    // End Items
    //

    //
    // Products
    //
    Route::post('/product', [ProductsController::class, 'store']);
    Route::put('/product/{product}', [ProductsController::class, 'update']);
    Route::delete('/product/{product}', [ProductsController::class, 'destroy']);
    Route::get('products', [ProductsController::class, 'index'])->name('product.allProducts');
    Route::get('products/{item_id}', [ProductsController::class, 'productsByItem'])->name('product.allProductsByItem');
    Route::get('filtered-products/{categoryIds?}/{itemIds?}', [ProductsController::class, 'filteredproducts'])->name('product.allFilteredProducts'); // for admin with all translations and all infos
    //
    // End Products
    //
});

//
// Auth
//
Route::post('/register', [AuthController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
//
// End Auth
//


//
//Categories
//
//some fields in dashboard must be shown: array of translations, where in clients must not
Route::get('category', [CategoriesController::class, 'index'])->name('category.allCategories'); //for dashboard
Route::get('category/allAvailable', [CategoriesController::class, 'allAvailable']); //for clients show
Route::get('/categories-names', [CategoriesController::class, 'getCategoriesNames']);

Route::get('category/{category}', [CategoriesController::class, 'show'])->name('category.singleCategory'); //for dashboard
Route::get('category/client/{category}', [CategoriesController::class, 'showOneForClients']); // for clients
//
//End Categories
//


//
// Items
//
//managed by ItemResource using when:
// Route::get('items', [ItemsController::class, 'index'])->name('item.allItems'); // for admin with all translations and all infos
// Route::get('filtered-items/{categoryId?}', [ItemsController::class, 'filteredItems'])->name('item.allFilteredItems'); // for admin with all translations and all infos
Route::get('items/allAvailable', [ItemsController::class, 'allAvailable']); //for clients show - specific info
Route::get('items/{category_id}', [ItemsController::class, 'itemsByCategory'])->name('item.allItemsByCategory');
Route::get('items/client/{category_id}', [ItemsController::class, 'itemsByCategoryforClient']);

Route::get('item/{item}', [ItemsController::class, 'show'])->name('item.singleitem'); //for dashboard
Route::get('item/client/{item}', [ItemsController::class, 'showOneForClients']); // for clients
Route::get('itemsNames', [ItemsController::class, 'getItemsNames']); // for clients
//
// End Items
//


//
// Products
//
//managed by ItemResource using when:
// Route::get('products', [ProductsController::class, 'index'])->name('product.allProducts');
// Route::get('filtered-products/{categoryIds?}/{itemIds?}', [ProductsController::class, 'filteredproducts'])->name('product.allFilteredProducts'); // for admin with all translations and all infos
// Route::get('products/{item_id}', [ProductsController::class, 'productsByItem'])->name('product.allProductsByItem');
Route::get('products/allAvailable', [ProductsController::class, 'allAvailable']); //for clients show
Route::get('products/client/{item_id}', [ProductsController::class, 'productsByItemforClient']);

Route::get('product/{product}', [ProductsController::class, 'show'])->name('product.singleproduct'); //for dashboard
Route::get('product/client/{product}', [ProductsController::class, 'showOneForClients']); // for clients
Route::get('product-sales', [ProductsController::class, 'getSales']); // for clients
//
// End Products
//
