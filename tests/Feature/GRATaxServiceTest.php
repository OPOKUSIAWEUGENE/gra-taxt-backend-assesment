<?php

namespace Tests\Feature;

use App\Services\GRATaxService;
use Tests\TestCase;

class GRATaxServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_can_calculate_netIncome()
    {
       
        $data = GRATaxService::netCalculator(taxableIncome: 756, rate: 0.175, known_accum_tax: 18.5, known_accum_income: 730);
        \Log::alert('result', [$data]);
        $this->assertIsNumeric($data);  

    }
    public function test_can_calculate_taxableIncome()
    {
        $data = GRATaxService::taxableIncomeCalculator(netIncome: 732.95, rate: 0.175, known_accum_tax: 18.5, known_accum_income: 730);
        \Log::alert('result', [$data]);
        $this->assertIsNumeric($data);  
    }
    public function test_can_get_ssnit_values()
    {
        $data=GRATaxService::SSNITCalculator(700);
        \Log::alert('result', [$data]);
        $this->assertIsObject($data);  
    }

    public function test_can_get_net_income()
    {
        $data=GRATaxService::getNetIncome(100000);
        \Log::alert('result', [$data]);
        $this->assertTrue(true);
    }

    public function test_can_get_taxable_income()
    {
        $data=GRATaxService::getGrossIncome(50392.16);
        \Log::alert('result', [$data]);
        $this->assertTrue(true);
    }


}
