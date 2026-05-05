<?php

namespace App\Legacy\Trash\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function createEmployee(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email'=> $request->email,
                'password'=>bcrypt('Password@123'),
            ]);

            $employee = EmployeeModel::create([
                'user_id'=>$user->id,
                'employee_code'=>$request->employee_code
            ]);

            DB::commit();

            return response()->json([
                'message'=>'Employee created',
                'data'=>$employee
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }
}
