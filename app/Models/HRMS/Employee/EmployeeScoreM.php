<?php

namespace App\Models\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\ScoreCategoryM;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeScoreM extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName() {
        return 'group_id';
    }

    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->format('d-m-Y h:m:s');
    }

    public function employee() {
        return $this->belongsTo(EmployeeM::class);
    }

    public function scoreCategory() {
        return $this->belongsTo(ScoreCategoryM::class);
    }

    public function scoredBy() {
        return $this->belongsTo(EmployeeM::class, 'scored_by');
    }

    public function getSimplifiedScores($params = [], $count = 10)
    {
        $query = $this->with('employee', 'scoredBy', 'scoreCategory');

        if (!auth()->user()->isAdmin()) {
            $query->where('employee_id', auth()->user()->employee->id);
        }

        // Filtering
        if (!empty($params['search'])) {
            $query->whereHas('employee', function ($q) use ($params) {
                $q->where('name', 'like', '%' . $params['search'] . '%');
            });
        }

        if (!empty($params['date_from'])) {
            $query->whereDate('created_at', '>=', $params['date_from']);
        }

        if (!empty($params['date_to'])) {
            $query->whereDate('created_at', '<=', $params['date_to']);
        }

        return $query->latest()->groupBy('group_id')->paginate($count);
    }

    public function getAverageScoreAttribute()
    {
        return self::where('group_id', $this->group_id)->avg('score');
    }

    public function getDataToCreate()
    {
        $data = [];

        $employees = EmployeeM::where('is_active', 1)->orderBy('name')->get();
        $scoreCategories = ScoreCategoryM::all();

        $data["employees"] = $employees;
        $data["scoreCategories"] = $scoreCategories;

        return $data;
    }

    public function getEmployeeScoreDetail($group_id = "")
    {
        return $this->with('scoreCategory')->where('group_id', $group_id)->get();
    }
}
