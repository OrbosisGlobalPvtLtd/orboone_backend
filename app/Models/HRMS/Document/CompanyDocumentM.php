<?php

namespace App\Models\HRMS\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDocumentM extends Model
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
