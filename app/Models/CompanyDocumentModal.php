<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDocumentModal extends Model
{

    

            protected $table = 'company_documents';

   protected $fillable = [
        'title','category','file_path',
        'visible_to','download_allowed','uploaded_by'
    ];

    protected $casts = [
        'visible_to' => 'array'
    ];
}
