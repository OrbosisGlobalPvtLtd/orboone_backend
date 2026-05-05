<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuM extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'name',
        'route',
        'icon',
        'module_key',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    public function children()
    {
        return $this->hasMany(MenuM::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(MenuM::class, 'parent_id');
    }

    protected static function newFactory()
    {
        return \Database\Factories\MenuFactory::new();
    }
}
