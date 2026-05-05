<?php

namespace App\Models\HRMS\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTypeM extends Model
{

        protected $table = 'document_types';

    protected $fillable = [
        'name','scope','is_mandatory','has_expiry'
    ];
}
