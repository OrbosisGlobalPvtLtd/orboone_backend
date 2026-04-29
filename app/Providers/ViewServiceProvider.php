<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\AccessControl\SidebarS;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {

            if (auth()->check()) {
                $menus = app(SidebarS::class)->getMenus(auth()->user());
            } else {
                $menus = [];
            }

            $view->with('menus', $menus);
        });
    }
}