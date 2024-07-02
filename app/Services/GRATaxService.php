<?php

namespace App\Services;

use App\Models\GRATaxConfig;
use App\Traits\Common;
use App\Traits\Errors;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GRATaxService
{
    use Common;
    use Errors;

    /**
     * Summary of getNetIncome
     * @param float $income
     * @param float $allowance
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNetIncome(float $income, float $allowance = 0): JsonResponse
    {
        try {

            //add allowance to basic income to get gross
            $gross = $income + $allowance;

            $pension = self::SSNITCalculator($gross);

            $last_tax_index = sizeof(GRATaxConfig::PAYE_TAX_BRACKET) - 1;

            if ($pension->amountLeft <= GRATaxConfig::PAYE_TAX_BRACKET[0]["cumulative_income"]) {

                return $this->success(
                    [
                        "tax_bracket" => GRATaxConfig::PAYE_TAX_BRACKET[0],
                        "pension" => $pension,
                        "taxable_income"=>$pension->amountLeft,
                        "netIncome" => $pension->amountLeft,
                    ]
                );
            }
            if ($pension->amountLeft > GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["cumulative_income"]) {

                return $this->success(
                    [
                        "tax_bracket" => GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index],
                        "taxable_income"=>$pension->amountLeft,
                        "pension" => $pension,
                        "netIncome" => self::netCalculator(taxableIncome: $pension->amountLeft, rate: GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["rate"], known_accum_tax: GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["cumulative_tax"], known_accum_income: GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["cumulative_income"]),
                    ]
                );
            }

            for ($i = 0; $i < $last_tax_index; $i++) {
                if (GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_income"] < $pension->amountLeft && $pension->amountLeft <= GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["cumulative_income"]) {
                    return $this->success(
                        [
                            "tax_bracket" => GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index],
                            "taxable_income"=>$pension->amountLeft,
                            "pension" => $pension,
                            "netIncome" => self::netCalculator(taxableIncome: $pension->amountLeft, rate: GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["rate"], known_accum_tax: GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_tax"], known_accum_income: GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_income"]),
                        ]
                    );
                }
            }

        } catch (Exception $e) {
            Log::error(__CLASS__, [$e]);
        }
        return $this->error();
    }

    /**
     * Summary of getGrossIncome
     * @param float $netIncome
     * @param float $allowance
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGrossIncome(float $netIncome, float $allowance = 0): JsonResponse
    {
        try {
            $last_tax_index = sizeof(GRATaxConfig::PAYE_TAX_BRACKET) - 1;
            if (($netIncome + GRATaxConfig::PAYE_TAX_BRACKET[0]["cumulative_tax"]) <= GRATaxConfig::PAYE_TAX_BRACKET[0]["cumulative_income"]) {

                $pension = self::SSNITReverseCalculator($netIncome);

                return $this->success(
                    [
                        "tax_bracket" => GRATaxConfig::PAYE_TAX_BRACKET[0],
                        "taxable_income"=>$netIncome,
                        "pension" => $pension,
                        "gross_ncome" => $pension->gross,
                        "allowance" => $allowance,
                        "basic_salary" => $pension->gross - $allowance,
                    ]
                );
            }
            if (($netIncome + GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["cumulative_tax"]) > GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["cumulative_income"]) {

                $taxableIncome = self::taxableIncomeCalculator(netIncome: $netIncome, rate: GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["rate"], known_accum_tax: GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["cumulative_tax"], known_accum_income: GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index]["cumulative_income"]);
                $pension = self::SSNITReverseCalculator($taxableIncome);
                return $this->success(
                    [
                        "tax_bracket" => GRATaxConfig::PAYE_TAX_BRACKET[$last_tax_index],
                        "pension" => $pension,
                        "taxable_income"=>$taxableIncome,
                        "gross_ncome" => $pension->gross,
                        "allowance" => $allowance,
                        "basic_salary" => $pension->gross - $allowance,
                    ]
                );
            }
            for ($i = 0; $i < $last_tax_index; $i++) {

                $income = $netIncome + GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["cumulative_tax"];

                if (GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_income"] < $income && $income <= GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["cumulative_income"]) {
                    $taxableIncome = self::taxableIncomeCalculator(netIncome: $netIncome, rate: GRATaxConfig::PAYE_TAX_BRACKET[$i + 1]["rate"], known_accum_tax: GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_tax"], known_accum_income: GRATaxConfig::PAYE_TAX_BRACKET[$i]["cumulative_income"]);
                    $pension = self::SSNITReverseCalculator($taxableIncome);
                    return $this->success(
                        [
                            "tax_bracket" => GRATaxConfig::PAYE_TAX_BRACKET[$i + 1],
                            "taxable_income"=>$taxableIncome,
                            "pension" => $pension,
                            "gross_ncome" => $pension->gross,
                            "allowance" => $allowance,
                            "basic_salary" => $pension->gross - $allowance,
                        ]
                    );
                }
            }

        } catch (Exception $e) {
            Log::error(__CLASS__, [$e]);
        }
        return $this->error();
    }

    /**
     * Summary of taxableIncomeCalculator
     * @param float $netIncome
     * @param float $rate
     * @param float $known_accum_tax
     * @param float $known_accum_income
     * @return float
     */
    public static function taxableIncomeCalculator(float $netIncome, float $rate, float $known_accum_tax, float $known_accum_income)
    {
        $result = ($netIncome + $known_accum_tax - ($rate / 100) * $known_accum_income) / (1 - $rate / 100);
        return round($result, 3);
    }

    public static function netCalculator(float $taxableIncome, float $rate, float $known_accum_tax, float $known_accum_income)
    {
        $result = $taxableIncome * (1 - $rate / 100) - $known_accum_tax + ($rate / 100) * $known_accum_income;
        return round($result, 3);}

    /**
     * Summary of SSNITCalculator
     * @param mixed $income
     * @return object
     */
    public static function SSNITCalculator($income)
    {
        $ssnit = [];
        $ssnitVal = 0;
        $ssnitRate = 0;

        foreach (GRATaxConfig::PENSION_TIER as $tier) {
            $employee = ($tier['employee'] / 100) * $income;
            $employer = ($tier['employer'] / 100) * $income;
            $ssnitVal += $employee + $employer;
            $ssnitRate = $ssnitRate + $tier['employee'] + $tier['employer'];
            array_push($ssnit, ["employee_rate" => $tier['employee'], "employee_amount" => round($employee,3), "employer_rate" => $tier['employer'], "employer_amount" => round($employer,3)]);
        }
        $amountLeft = $income - $ssnitVal;

        return (object) ["ssnit" => $ssnit, "ssnitVal" => round($ssnitVal,3), "ssnitRate" => $ssnitRate, "amountLeft" => round($amountLeft,3)];
    }

   
    /**
     * Summary of SSNITReverseCalculator
     * @param mixed $income
     * @return object
     */
    public static function SSNITReverseCalculator($income):object
    {
        $ssnit = [];
        $ssnitVal = 0;
        $ssnitRate = 0;
        foreach (GRATaxConfig::PENSION_TIER as $tier)  {
            $ssnitRate +=$tier['employee'] + $tier['employer'];
        
        }
        $gross=(100*$income)/(100-$ssnitRate);
       
        foreach (GRATaxConfig::PENSION_TIER as $tier) {
            $employee = ($tier['employee'] / 100) * $gross;
            $employer = ($tier['employer'] / 100) * $gross;
            $ssnitVal += $employee + $employer;
            $ssnitRate = $ssnitRate + $tier['employee'] + $tier['employer'];
            array_push($ssnit, ["employee_rate" => $tier['employee'], "employee_amount" => round($employee,3), "employer_rate" => $tier['employer'], "employer_amount" => round($employer,3)]);
        }
        // $gross = $income + $ssnitVal;

        return (object) ["ssnit" => $ssnit, "ssnitVal" => round($ssnitVal,3), "ssnitRate" => $ssnitRate, "gross" => round($gross,3)];
    }

}
