<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTypeModal extends Model
{

        protected $table = 'document_types';

    protected $fillable = [
        'name','scope','is_mandatory','has_expiry'
    ];
}
