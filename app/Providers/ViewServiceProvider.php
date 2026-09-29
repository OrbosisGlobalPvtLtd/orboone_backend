<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\AccessControl\SidebarS;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('components.sidebar', function ($view) {
            // Respect explicit component data; only resolve menus when the caller omitted them.
            if (! array_key_exists('menus', $view->getData())) {
                $menus = auth()->check()
                    ? app(SidebarS::class)->getMenus(auth()->user())
                    : collect();

                $view->with('menus', $menus);
            }
        });
    }
}
