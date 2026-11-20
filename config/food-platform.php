<?php

return [
    'gst_percent' => (float) env('FOOD_GST_PERCENT', 5),
    'platform_fee_percent' => (float) env('FOOD_PLATFORM_FEE_PERCENT', 2),
    'commission_percent' => (float) env('RESTAURANT_COMMISSION_PERCENT', 15),
    'membership_monthly_price' => (float) env('FOOD_MEMBERSHIP_MONTHLY_PRICE', 199),
];
