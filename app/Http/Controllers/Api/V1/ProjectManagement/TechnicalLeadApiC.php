<?php

namespace App\Http\Controllers\Api\V1\ProjectManagement;

use App\Http\Controllers\Api\V1\Reporting\ReportingApiC;
use Illuminate\Http\Request;

class TechnicalLeadApiC extends ReportingApiC
{
    /**
     * Legacy Alias for developers roster
     */
    public function developers(Request $request)
    {
        return $this->myEmployees($request);
    }
}
