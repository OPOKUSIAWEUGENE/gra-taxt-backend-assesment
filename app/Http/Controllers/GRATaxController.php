<?php

namespace App\Http\Controllers;

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
        $data=$request->only(['gross_salary']);
        return $this->service->getNetIncome($data);
    }

    /**
     * get gross salary from net income
     * 
     * @param GRATaxRequest $request
     * @return JsonResponse
     */
    public function getGrossSalary(GRATaxRequest $request):JsonResponse
    {
        $data=$request->only(['net_salary','allowance']);
        return $this->service->getGrossIncome($data);
    }
}
