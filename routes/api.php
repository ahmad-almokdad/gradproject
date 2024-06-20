<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\ProviderController;
use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\User\UAuthController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\Provider\OrderController as ProviderOrderController;
use App\Http\Controllers\Api\Provider\ProviderController as ProviderProviderController;
use App\Http\Controllers\Api\User\FavoriteController;
use App\Http\Controllers\Api\User\OrderController;
use App\Http\Controllers\Api\User\GetProviderController;
use App\Http\Controllers\Api\User\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// All routers // all api here must be api authoenti

Route::group(['middleware' => ['api'/*,'checkPassword'*/], 'namespace' => 'Api'], function () {
    Route::post('/get_main_categories', [CategoriesController::class, 'index']);
    Route::post('/get-catergory-byId', [CategoriesController::class, 'getCategoryById']);
    Route::post('/change-category-status', [CategoriesController::class, 'changeStatus']);

    Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:admin-api');

        Route::post('/add-provider', [ProviderController::class, 'add_provider'])->middleware('auth:admin-api');
        Route::post('assign-services-to-providers', [ProviderController::class, 'assignServiceToProvider'])->middleware('auth:admin-api');
        Route::post('change-active-providers', [ProviderController::class, 'changeActiveProvider'])->middleware('auth:admin-api');

        Route::post('/add-service', [ServiceController::class, 'addService']);
    });

    Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
        Route::post('login', [UAuthController::class, 'login']);
        Route::post('register', [UAuthController::class, 'register']);

        Route::get('/get-profile', [UAuthController::class, 'getProfile'])->middleware('auth:user-api');
        Route::post('/edit-profile', [UAuthController::class, 'editProfile'])->middleware('auth:user-api');
        Route::get('/get-services', [ServiceController::class, 'index']);
        // Route::post('/edit-profile',);
        Route::post('/change-password', [UAuthController::class, 'changePassword'])->middleware('auth:user-api');
        Route::get('orders', [OrderController::class, 'index'])->middleware('auth:user-api');
        Route::post('orders', [OrderController::class, 'store'])->middleware('auth:user-api');
        Route::post('orders/request-payment', [OrderController::class, 'approve_order'])->middleware(['auth:user-api','throttle:5,5']);
        Route::post('orders/confirm-payment', [OrderController::class, 'approve_payment_order'])->middleware(['auth:user-api','throttle:3,5']);
        Route::get('/get-providers', [ProviderProviderController::class, 'index']);
        Route::post('/logout', [UAuthController::class, 'logout'])->middleware('auth:user-api');




        //!
        Route::post('favorite', [FavoriteController::class, 'AddOrRemoveFavorite'])->middleware('auth:user-api');
        Route::get('show-favorites', [FavoriteController::class, 'ShowFavorite'])->middleware('auth:user-api');
        Route::post('add-review', [ReviewController::class, 'CreateReviewRating'])->middleware('auth:user-api');


        Route::get('show-review', [ReviewController::class, 'ShowReviewAll'])->middleware('auth:user-api');
        Route::get('get-review', [ReviewController::class, 'GetReview'])->middleware('auth:user-api');
        Route::post('delete-review', [ReviewController::class, 'DeleteReview'])->middleware('auth:user-api');

        //!
        Route::get('/get-provider-id/{id}', [GetProviderController::class, 'GetProvider_ByID']);

        // Route::post('/edit-profile',);
    });

    Route::group(['prefix' => 'provider', 'namespace' => 'Provider'], function () {
        Route::post('login', [ProviderProviderController::class, 'login']);
        Route::post('add-price', [ProviderOrderController::class, 'addPriceToOrder'])->middleware('auth:provider');
        Route::get('orders', [ProviderOrderController::class, 'indexByStatus'])->middleware('auth:provider');
        Route::get('profile', [ProviderProviderController::class, 'getProfile'])->middleware('auth:provider');
        Route::get('complete-order',[ProviderOrderController::class, 'makeOrderComplete'])->middleware('auth:provider');
        // Route::post('register', [UAuthController::class, 'register']);
        // Route::post('')

        // Route::post('/logout', [UAuthController::class, 'logout'])->middleware('auth:user-api');
    });


    Route::group(['prefix' => 'user', 'middleware' => 'auth.guard:user-api'], function () {
        Route::post('profile', function () {
            return 'Only authenticated user can reach me';
        });
    });
});

Route::group(['middleware' => ['api', 'checkPassword', 'checkAdminToken:admin-api'], 'namespace' => 'Api'], function () {
    Route::get('offers', [CategoriesController::class, 'index']);
});


/*Route::middleware('auth:api')->prefix('v1')->group(function() {
    Route::get('/user', function(Request $request) {
        return $request->user();
    });

   // Route::get('/authors/{author}', [AuthorsController::class, 'show']);

    //Route::get('/authors', [AuthorsController::class, 'index']);

    Route::apiResource('/authors', AuthorsController::class);
    Route::apiResource('/books', BooksController::class);
}); */
