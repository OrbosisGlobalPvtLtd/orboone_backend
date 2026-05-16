<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolicyChangeLogC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $query = DB::table('policy_change_logs')
            ->leftJoin('users', 'users.id', '=', 'policy_change_logs.changed_by_user_id')
            ->select('policy_change_logs.*', 'users.name as changed_by_name');
        $this->applyCommonFilters($query, $request, ['dateColumn' => 'policy_change_logs.created_at', 'filterMap' => ['policy_type' => 'policy_change_logs.policy_type', 'changed_by_user' => 'policy_change_logs.changed_by_user_id']]);
        return view('settings.policy_change_logs.index', [
            'accesses' => $this->accesses(), 'active' => 'settings', 'pageTitle' => 'Policy Change Logs',
            'pageSubtitle' => 'Read-only audit trail of policy changes.',
            'rows' => $query->latest('policy_change_logs.id')->paginate(50),
            'columns' => [['key' => 'policy_type', 'label' => 'Policy Type'], ['key' => 'policy_id', 'label' => 'Policy ID'], ['key' => 'changed_by_name', 'label' => 'Changed By'], ['key' => 'old_values', 'label' => 'Old Values', 'type' => 'json'], ['key' => 'new_values', 'label' => 'New Values', 'type' => 'json'], ['key' => 'created_at', 'label' => 'Changed At', 'type' => 'datetime']],
            'filters' => [['name' => 'policy_type', 'label' => 'Policy Type', 'type' => 'select', 'options' => DB::table('policy_change_logs')->whereNotNull('policy_type')->distinct()->pluck('policy_type', 'policy_type')->toArray()], ['name' => 'changed_by_user', 'label' => 'Changed By User ID', 'type' => 'number'], ['name' => 'from', 'label' => 'From', 'type' => 'date'], ['name' => 'to', 'label' => 'To', 'type' => 'date']],
            'canCreate' => false,
        ]);
    }
}
