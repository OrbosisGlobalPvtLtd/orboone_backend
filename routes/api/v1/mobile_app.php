<?php

use App\Http\Controllers\Api\V1\MobileApp\MobileAppVersionApiC;
use Illuminate\Support\Facades\Route;

Route::get('/mobile-app/latest-version', [MobileAppVersionApiC::class, 'latest']);
