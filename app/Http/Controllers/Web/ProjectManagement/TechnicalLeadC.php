<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Web\Reporting\ReportingC;

class TechnicalLeadC extends ReportingC
{
    /**
     * Legacy Alias Method for Developers Roster
     */
    public function developers(\Illuminate\Http\Request $request)
    {
        return $this->assignments($request);
    }

    /**
     * Legacy Alias Method for Assigning Developer
     */
    public function assignDeveloper(\Illuminate\Http\Request $request)
    {
        return $this->assignSupervisor($request);
    }

    /**
     * Legacy Alias Method for Relieving Developer
     */
    public function relieveDeveloper(\Illuminate\Http\Request $request, $id)
    {
        return $this->relieveEmployee($request, $id);
    }
}
