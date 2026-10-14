<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('products', function () {
    echo "List of products";
});

Route::get("cart", function () {   
    echo "List of cart";
});

Route::get("checkout", function () {   
    echo "Checkout page";
});


