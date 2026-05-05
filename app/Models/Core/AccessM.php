<?php

namespace App\Models\Core;

use App\Models\Core\MenuM as Menu;
use App\Models\Core\RoleM as Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Stmt\Foreach_;

class AccessM extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'accesses';

    protected static function newFactory()
    {
        return \Database\Factories\AccessFactory::new();
    }

    public function menu() {
        return $this->belongsTo(Menu::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function get($only_active_menu = false) {
        $accesses = $this->with('menu', 'role')->orderBy('menu_id', 'ASC')->get();

        $menus = $this->sortMenus($accesses, $only_active_menu);

        $checkedMenus = $this->checkRole($menus);

        return $checkedMenus;
    }

    public function sortMenus ($accesses, $only_active_menu = false) {
        $menus = [];

        foreach($accesses as $access) {
            if($only_active_menu == true) {

                if ($access->menu->is_active == false) {
                    continue;
                }

                if($access->menu->parent_id == 0) 
                    array_push($menus, $access);
                else {
                    $key = array_search($access->menu->parent_id, array_column($menus, 'menu_id'));

                    $menus[$key]["subMenu"] = $access;
                }
            }
        }

        return $menus;
    }

    public function checkRole($menus) {
        $checkedMenus = [];

        foreach($menus as $menu) {
            if($menu->role_id == auth()->user()->role_id && $menu->status > 0) 
                array_push($checkedMenus, $menu);
        }

        return $checkedMenus;
    }
}
