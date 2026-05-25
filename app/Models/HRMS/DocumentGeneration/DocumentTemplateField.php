<?php

namespace App\Models\HRMS\DocumentGeneration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplateField extends Model
{
    use HasFactory;

    protected $table = 'document_template_fields';
    protected $guarded = [];
    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }
}
