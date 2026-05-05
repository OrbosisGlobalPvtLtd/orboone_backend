<?php

namespace App\Http\Controllers\Api\V1\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\ProjectManagement\TaskmanagementModel;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function myTasks()
    {
        $tasks = TaskmanagementModel::where('user_id', auth()->id())->get();

        return response()->json([
            'status'=>true,
            'data'=>$tasks
        ]);
    }
}