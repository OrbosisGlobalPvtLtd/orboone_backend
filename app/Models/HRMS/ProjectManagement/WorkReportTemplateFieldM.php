<?php

namespace App\Models\HRMS\ProjectManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkReportTemplateFieldM extends Model
{
    use HasFactory;

    protected $table = 'work_report_template_fields';

    protected $fillable = [
        'template_id',
        'field_key',
        'field_label',
        'field_type',
        'placeholder',
        'options_json',
        'validation_json',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options_json' => 'array',
        'validation_json' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(WorkReportTemplateM::class, 'template_id');
    }
}
