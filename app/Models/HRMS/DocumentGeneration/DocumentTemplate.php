<?php

namespace App\Models\HRMS\DocumentGeneration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\UserM;

class DocumentTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_templates';
    protected $guarded = [];
    protected $casts = [
        'detected_fields' => 'array',
        'detected_placeholders' => 'array',
        'invalid_placeholders' => 'array',
        'missing_required_placeholders' => 'array',
        'unknown_placeholders' => 'array',
        'placeholder_mapping' => 'array',
        'is_active' => 'boolean',
        'is_certificate' => 'boolean',
        'requires_review' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(DocumentTemplateField::class, 'template_id')->orderBy('sort_order');
    }

    public function createdBy()
    {
        return $this->belongsTo(UserM::class, 'created_by_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(UserM::class, 'updated_by_user_id');
    }
}
