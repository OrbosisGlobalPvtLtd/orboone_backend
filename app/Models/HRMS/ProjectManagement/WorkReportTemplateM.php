<?php

namespace App\Models\HRMS\ProjectManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\UserM as User;

class WorkReportTemplateM extends Model
{
    use HasFactory;

    protected $table = 'work_report_templates';

    protected $fillable = [
        'project_id',
        'name',
        'code',
        'employee_role_type',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectM::class, 'project_id');
    }

    public function fields()
    {
        return $this->hasMany(WorkReportTemplateFieldM::class, 'template_id')->orderBy('sort_order')->orderBy('id');
    }

    public function activeFields()
    {
        return $this->hasMany(WorkReportTemplateFieldM::class, 'template_id')->where('is_active', 1)->orderBy('sort_order')->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
