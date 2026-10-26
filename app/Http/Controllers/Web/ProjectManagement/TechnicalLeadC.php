<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Web\Reporting\ReportingC;
use Illuminate\Http\Request;

class TechnicalLeadC extends ReportingC
{
    /**
     * Legacy Alias Method for Developers Roster
     */
    public function developers(Request $request)
    {
        return $this->assignments($request);
    }

    /**
     * Legacy Alias Method for Assigning Developer
     */
    public function assignDeveloper(Request $request)
    {
        return $this->assignSupervisor($request);
    }

    /**
     * Legacy Alias Method for Relieving Developer
     */
    public function relieveDeveloper(Request $request, $id)
    {
        return $this->relieveEmployee($request, $id);
    }
}
