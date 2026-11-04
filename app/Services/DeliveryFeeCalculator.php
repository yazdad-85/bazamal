<?php

namespace App\Services;

use App\Models\DeliveryVillage;
use App\Models\Setting;

class DeliveryFeeCalculator
{
    /**
     * Fee for a village, or null while a paid village's distance or the rate has not been configured.
     */
    public function feeFor(DeliveryVillage $village): ?int
    {
        if ($village->hasFreeDelivery()) {
            return 0;
        }

        $ratePerKm = Setting::getValue('delivery_rate_per_km');

        if ($village->distance_km === null || $ratePerKm === null || $ratePerKm === '') {
            return null;
        }

        $fee = (float) $village->distance_km * (int) $ratePerKm;
        $rounding = (int) Setting::getValue('delivery_fee_rounding', '0');

        if ($rounding > 0) {
            $fee = ceil(round($fee / $rounding, 6)) * $rounding;
        }

        return max((int) ceil($fee), (int) Setting::getValue('delivery_min_fee', '0'));
    }
}
