<?php

namespace App\Models\HRMS\DocumentGeneration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;

class GeneratedDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'generated_documents';
    protected $guarded = [];
    protected $casts = [
        'field_values' => 'array',
        'form_data' => 'array',
        'placeholder_values' => 'array',
        'missing_placeholders' => 'array',
        'invalid_placeholders' => 'array',
        'unknown_placeholders' => 'array',
        'generation_snapshot' => 'array',
        'reviewed_at' => 'datetime',
        'sent_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id')->withTrashed();
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(UserM::class, 'user_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(UserM::class, 'generated_by_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(UserM::class, 'generated_by');
    }

    public function logs()
    {
        return $this->hasMany(GeneratedDocumentLog::class, 'generated_document_id')->latest();
    }
}
