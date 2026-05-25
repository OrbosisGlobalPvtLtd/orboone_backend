<?php

namespace App\Models\HRMS\DocumentGeneration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\UserM;

class GeneratedDocumentLog extends Model
{
    use HasFactory;

    protected $table = 'generated_document_logs';
    protected $guarded = [];
    protected $casts = [
        'metadata' => 'array',
    ];

    public function generatedDocument()
    {
        return $this->belongsTo(GeneratedDocument::class, 'generated_document_id');
    }

    public function actor()
    {
        return $this->belongsTo(UserM::class, 'actor_user_id');
    }
}
