<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('routeActive')) {

    function routeActive($route)
    {
        return Request::routeIs($route) ? 'active' : '';
    }
}
