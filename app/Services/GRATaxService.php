<?php

namespace App\Services;

use App\Models\GRATaxConfig;

class GRATaxService
{

    public static function getNetIncome(float $income, float $allowance = 0)
    {
        $gross = $income + $allowance;
        $pension = self::SSNITCalculator($gross);

        $last_taxt_index = sizeof(GRATaxConfig::PAYE_TAX_BRACKET) - 1;
        if ($pension->amountLeft <= GRATaxConfig::PAYE_TAX_BRACKET[0]["cumulative_income"]) {
            \Log::alert('nothin happened',[GRATaxConfig::PAYE_TAX_BRACKET[0] , 'amount'=>$pension]);
            return $pension->amountLeft;
        }
        if ($pension->amountLeft > GRATaxConfig::PAYE_TAX_BRACKET[$last_taxt_index]["cumulative_income"]) {

            return self::netCalculator(taxableIncome: $pension->amountLeft, rate: GRATaxConfig::PAYE_TAX_BRACKET[$last_taxt_index]["rate"], known_accum_tax: GRATaxConfig::PAYE_TAX_BRACKET[$last_taxt_index]["cumulative_tax"], known_accum_income: GRATaxConfig::PAYE_TAX_BRACKET[$last_taxt_index]["cumulative_income"]);
        }

        for ($i = 0; $i < $last_taxt_index; $i++) {

           
            if (GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_income"] < $pension->amountLeft && $pension->amountLeft <= GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["cumulative_income"]) {
                \Log::alert('nothin happened',[GRATaxConfig::PAYE_TAX_BRACKET[$i] , 'amount'=>$pension]);
                return self::netCalculator(taxableIncome: $pension->amountLeft, rate: GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["rate"], known_accum_tax: GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_tax"], known_accum_income: GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_income"]);

            }
        }
        \Log::alert('nothin happened');

    }

    public static function getGrossIncome(float $netIncome, float $allowance=0)
    {

        $last_taxt_index = sizeof(GRATaxConfig::PAYE_TAX_BRACKET) - 1;
        if ($netIncome <= GRATaxConfig::PAYE_TAX_BRACKET[0]["cumulative_income"]) {
            \Log::alert('nothin happened',[GRATaxConfig::PAYE_TAX_BRACKET[0] , 'amount'=>$netIncome]);
            return $netIncome;
        }
        if ($netIncome > GRATaxConfig::PAYE_TAX_BRACKET[$last_taxt_index]["cumulative_income"]) {

            return self::taxableIncomeCalculator(netIncome: $netIncome, rate: GRATaxConfig::PAYE_TAX_BRACKET[$last_taxt_index]["rate"], known_accum_tax: GRATaxConfig::PAYE_TAX_BRACKET[$last_taxt_index]["cumulative_tax"], known_accum_income: GRATaxConfig::PAYE_TAX_BRACKET[$last_taxt_index]["cumulative_income"]);
        }
        for ($i = 0; $i < $last_taxt_index; $i++) {

           
            if (GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_income"] < $netIncome && $netIncome <= GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["cumulative_income"]) {
                \Log::alert('nothin happened',[GRATaxConfig::PAYE_TAX_BRACKET[$i]]);
                return self::taxableIncomeCalculator(netIncome: $netIncome, rate: GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["rate"], known_accum_tax: GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_tax"], known_accum_income: GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_income"]);

            }
        }
    }

    public static function taxableIncomeCalculator(float $netIncome, float $rate, float $known_accum_tax, float $known_accum_income)
    {
        $result = ($netIncome + $known_accum_tax - ($rate/100) * $known_accum_income) / (1 - $rate/100);
        return round($result, 2);
    }

    public static function netCalculator(float $taxableIncome, float $rate, float $known_accum_tax, float $known_accum_income)
    {\Log::alert('these are the income',[
        "taxableIncome"=>$taxableIncome,
        "rate"=>$rate,
        "accumIncome"=>$known_accum_income,
        "tax"=>$known_accum_tax
    ]);
        $result = $taxableIncome * (1 - $rate/100) - $known_accum_tax + ($rate/100) * $known_accum_income;
        return round($result, 2);
    }

    public static function SSNITCalculator($income)
    {
        $ssnit = [];
        $ssnitVal = 0;
        $ssnitRate = 0;

        foreach (GRATaxConfig::PENSION_TIER as $tier) {
            $employee = ($tier['employee'] / 100) * $income;
            $employer = ($tier['employer'] / 100) * $income;
            $ssnitVal = $ssnitVal + ($employee + $employer);
            $ssnitRate = $ssnitRate + $tier['employee'] + $tier['employer'];
            array_push($ssnit, ["employee_rate" => $tier['employee'], "employee_amount" => $employee, "employer_rate" => $tier['employer'], "employer_amount" => $employer]);
        }
        $amountLeft = $income - $ssnitVal;

        return (object) ["ssnit" => $ssnit, "ssnitVal" => $ssnitVal, "ssnitRate" => $ssnitRate, "amountLeft" => $amountLeft];
    }

}
