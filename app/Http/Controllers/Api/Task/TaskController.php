<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Models\TaskmanagementModel;
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