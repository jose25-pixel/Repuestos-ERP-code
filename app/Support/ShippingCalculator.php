<?php

namespace App\Support;

class ShippingCalculator
{
    public static function calculate(int $itemCount, float $totalWeightKg, string $department, bool $isHeavy = false): array
    {
        $farDepartments = ['la_union', 'morazan', 'ahuachapan', 'cabanas', 'santa_ana'];
        $baseCost = 5.00;
        $department = strtolower(trim($department));

        $shippingCost = in_array($department, $farDepartments, true) ? 6.00 : $baseCost;

        if ($itemCount > 5) {
            $shippingCost += 1.00;
        }

        if ($itemCount > 15) {
            $shippingCost += 2.00;
        }

        if ($totalWeightKg > 10 || $isHeavy) {
            $shippingCost += 2.00;
        }

        return [
            'cost' => round($shippingCost, 2),
            'department' => $department,
            'is_heavy' => $totalWeightKg > 10 || $isHeavy,
        ];
    }
}
