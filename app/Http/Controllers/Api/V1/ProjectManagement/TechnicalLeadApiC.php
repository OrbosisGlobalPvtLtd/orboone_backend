<?php

namespace App\Http\Controllers\Api\V1\ProjectManagement;

use App\Http\Controllers\Api\V1\Reporting\ReportingApiC;

class TechnicalLeadApiC extends ReportingApiC
{
    /**
     * Legacy Alias for developers roster
     */
    public function developers(\Illuminate\Http\Request $request)
    {
        return $this->myEmployees($request);
    }
}
