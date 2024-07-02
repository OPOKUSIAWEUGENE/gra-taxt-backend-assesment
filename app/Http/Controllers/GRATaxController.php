<?php

namespace App\Http\Controllers;

use App\Http\Requests\GRAGrossRequest;
use App\Http\Requests\GRATaxRequest;
use App\Services\GRATaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GRATaxController extends Controller
{
    
    /**
     * 
     * 
     * @param \App\Services\GRATaxService $service
     */
    public function __construct(protected GRATaxService $service)
    {}



    /**
     * get net income from gross salary
     * 
     * @param GRATaxRequest $request
     * @return JsonResponse
     */
    public function getNetIncome(GRATaxRequest $request):JsonResponse
    {
        \Log::alert($request->gross_salary);
    
        return $this->service->getNetIncome($request->gross_salary, $request->allowance);
    }

    /**
     * Summary of getGrossSalary
     * @param \App\Http\Requests\GRAGrossRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGrossSalary(GRAGrossRequest $request):JsonResponse
    {

        return $this->service->getGrossIncome($request->net_salary, $request->allowance);
    }
}
