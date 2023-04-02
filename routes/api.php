<?php

use App\Http\Controllers\Api\DashboarController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix("v1")->name("login.v1")->group(function() {
    Route::get("/auth/products", [DashboardController::class, "getproducts"]);
    Route::get("/auth/user-profile", [AuthController::class, "userProfile"]);
    Route::post("/login", [AuthController::class, "login"]);
    Route::post("auth/logout", [AuthController::class, "logout"]);
    Route::post("auth/profile", [AuthController::class, "changeProfile"]);
    Route::get("/data/profile", [AuthController::class, "getProfile"]);
    //log 
    Route::get("user/logs", [AuthController::class, "getLogs"]);
    // Route::post("Admin/store", [DashboarController::class, "createproduct"]);
    Route::post("auth/store", [DashboardController::class, "createproduct"]);
    Route::get("product/search", [DashboardController::class, "searchProducts"]);
    Route::delete("auth/delete/{id}", [DashboardController::class, "destroy"]);
    //customer
    Route::get("/auth/customers", [DashboardController::class, "getCustomers"]);
    Route::get("/auth/customer/{id}", [DashboardController::class, "showInvoice"]);
    Route::get("customer/search", [DashboardController::class, "searchCustomer"]);
    Route::post("store/customer", [DashboardController::class, "createCustomer"]);
    Route::get("customer/analytic", [DashboardController::class, "getCustomerAnalytics"]);
    Route::get("customer/chart", [DashboardController::class, "getCustomerCharts"]);
    //order
    Route::post("store/order", [DashboardController::class, "createOrder"]);
    Route::get("order/hasInvoice", [DashboardController::class, "hasInvoice"]);
    //invoice
    Route::post("store/invoice", [DashboardController::class, "createInvoice"]);
    //companies 
    Route::post("Admin/company", [DashboardController::class, "createCompany"]);
    //payment
    Route::get("payment/customer", [DashboardController::class, "getallclienthasinvoice"]);

});

