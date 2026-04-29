<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerModel extends Model
{
    const TABLE = 'customer';
    protected $table = self::TABLE;
    protected $fillable = [

        'name',
        'email',
        'phone',
        'gst_number',
        'pan_card',
        'display_name',
        'company_name',
        'address'

    ];

    public $timestamps =true;

}
