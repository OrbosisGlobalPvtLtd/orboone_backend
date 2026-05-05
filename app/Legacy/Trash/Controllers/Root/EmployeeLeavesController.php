<?php

namespace App\Legacy\Trash\Controllers\Root;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Leave\EmployeeLeaveM as EmployeeLeave;
use Illuminate\Http\Request;

class EmployeeLeavesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HRMS\Leave\EmployeeLeaveM  $employeeLeave
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeLeave $employeeLeave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HRMS\Leave\EmployeeLeaveM  $employeeLeave
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeLeave $employeeLeave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HRMS\Leave\EmployeeLeaveM  $employeeLeave
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeLeave $employeeLeave)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HRMS\Leave\EmployeeLeaveM  $employeeLeave
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeLeave $employeeLeave)
    {
        //
    }
}
