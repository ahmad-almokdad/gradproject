<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\User\UAuthController;
use App\Http\Controllers\Api\CategoriesController;
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

        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.guard:admin-api');
    });

    Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
        Route::post('login', [UAuthController::class, 'login']);
        Route::post('register', [UAuthController::class, 'register']);

        Route::post('/logout', [UAuthController::class, 'logout'])->middleware('auth:user-api');
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