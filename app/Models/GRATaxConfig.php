<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GRATaxConfig extends Model
{
/**
 * pension tiers
 */
    const PENSION_TIER = [
        [
            "tier" => 1, "employee" => 0, "employer" => 13,
        ],
        [
            "tier" => 2, "employee" => 5.5, "employer" => 0,
        ],
        [
            "tier" => 3, "employee" => 5, "employer" => 5,
        ],
    ];

    const PAYE_TAX_BRACKET = [
        [
            "chargeable_income" => 490, "rate" => 0, "tax_payable" => 0, "cumulative_income" => 490, "cumulative_tax" => 0,
        ],
        [
            "chargeable_income" => 110, "rate" => 5, "tax_payable" => 5.5, "cumulative_income" => 600, "cumulative_tax" => 5.5,
        ],
        [
            "chargeable_income" => 130, "rate" => 10, "tax_payable" => 13, "cumulative_income" => 730, "cumulative_tax" => 18.5,
        ],
        [
            "chargeable_income" => 3166.67, "rate" => 17.5, "tax_payable" => 554.17, "cumulative_income" => 3896.67, "cumulative_tax" => 572.67,
        ],
        [
            "chargeable_income" => 16000, "rate" => 25, "tax_payable" => 4000, "cumulative_income" => 19896.67, "cumulative_tax" => 4572.67,
        ],
        [
            "chargeable_income" => 30520, "rate" => 30, "tax_payable" => 9156, "cumulative_income" => 50416.67, "cumulative_tax" => 13728.67,
        ],
        [
            "chargeable_income" => 50000, "rate" => 35, "tax_payable" => 17500, "cumulative_income" => 100416.67, "cumulative_tax" => 31228.67,
        ],
    ];
}
