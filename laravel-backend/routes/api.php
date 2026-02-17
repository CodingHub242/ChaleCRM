<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/login', [ApiController::class, 'login']);
Route::post('/auth/register', [ApiController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [ApiController::class, 'logout']);
    Route::get('/auth/user', [ApiController::class, 'user']);
    
    // Dashboard
    Route::get('/dashboard', [ApiController::class, 'dashboard']);
    
    // Contacts
    Route::get('/contacts', [ApiController::class, 'contacts']);
    Route::get('/contacts/{id}', [ApiController::class, 'contact']);
    Route::post('/contacts', [ApiController::class, 'createContact']);
    Route::put('/contacts/{id}', [ApiController::class, 'updateContact']);
    Route::delete('/contacts/{id}', [ApiController::class, 'deleteContact']);
    
    // Companies
    Route::get('/companies', [ApiController::class, 'companies']);
    Route::get('/companies/{id}', [ApiController::class, 'company']);
    Route::post('/companies', [ApiController::class, 'createCompany']);
    Route::put('/companies/{id}', [ApiController::class, 'updateCompany']);
    Route::delete('/companies/{id}', [ApiController::class, 'deleteCompany']);
    
    // Deals
    Route::get('/deals', [ApiController::class, 'deals']);
    Route::get('/deals/{id}', [ApiController::class, 'deal']);
    Route::post('/deals', [ApiController::class, 'createDeal']);
    Route::put('/deals/{id}', [ApiController::class, 'updateDeal']);
    Route::put('/deals/{id}/stage', [ApiController::class, 'updateDealStage']);
    Route::delete('/deals/{id}', [ApiController::class, 'deleteDeal']);
    
    // Tasks
    Route::get('/tasks', [ApiController::class, 'tasks']);
    Route::get('/tasks/{id}', [ApiController::class, 'task']);
    Route::post('/tasks', [ApiController::class, 'createTask']);
    Route::put('/tasks/{id}', [ApiController::class, 'updateTask']);
    Route::put('/tasks/{id}/complete', [ApiController::class, 'completeTask']);
    Route::delete('/tasks/{id}', [ApiController::class, 'deleteTask']);
    
    // Activities
    Route::get('/activities', [ApiController::class, 'activities']);
    Route::post('/activities', [ApiController::class, 'createActivity']);
    Route::delete('/activities/{id}', [ApiController::class, 'deleteActivity']);
    
    // Products
    Route::get('/products', [ApiController::class, 'products']);
    Route::get('/products/{id}', [ApiController::class, 'product']);
    Route::post('/products', [ApiController::class, 'createProduct']);
    Route::put('/products/{id}', [ApiController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ApiController::class, 'deleteProduct']);
    
    // Quotes
    Route::get('/quotes', [ApiController::class, 'quotes']);
    Route::get('/quotes/{id}', [ApiController::class, 'quote']);
    Route::post('/quotes', [ApiController::class, 'createQuote']);
    Route::put('/quotes/{id}', [ApiController::class, 'updateQuote']);
    Route::delete('/quotes/{id}', [ApiController::class, 'deleteQuote']);
    
    // Invoices
    Route::get('/invoices', [ApiController::class, 'invoices']);
    Route::get('/invoices/{id}', [ApiController::class, 'invoice']);
    Route::post('/invoices', [ApiController::class, 'createInvoice']);
    Route::put('/invoices/{id}', [ApiController::class, 'updateInvoice']);
    Route::delete('/invoices/{id}', [ApiController::class, 'deleteInvoice']);
    
    // Users
    Route::get('/users', [ApiController::class, 'users']);

    // File Upload
    Route::post('/upload/photo', [ApiController::class, 'uploadPhoto']);
});
