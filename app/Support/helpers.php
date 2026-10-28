<?php

if (! function_exists('vnd')) {
    /** Định dạng tiền VND: 1.250.000.000 ₫ */
    function vnd(int|float|null $amount): string
    {
        return number_format((float) $amount, 0, ',', '.').' ₫';
    }
}

if (! function_exists('vnd_short')) {
    /** Định dạng gọn: 1,25 tỷ / 545 triệu */
    function vnd_short(int|float|null $amount): string
    {
        $amount = (float) $amount;
        if ($amount >= 1_000_000_000) {
            return rtrim(rtrim(number_format($amount / 1_000_000_000, 3, ',', '.'), '0'), ',').' tỷ';
        }
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 0, ',', '.').' triệu';
        }

        return vnd($amount);
    }
}

if (! function_exists('body_type')) {
    /** Kiểu dáng xe để vẽ hình minh họa theo dòng xe. */
    function body_type(?string $categorySlug): string
    {
        return match ($categorySlug) {
            'suv-crossover' => 'suv',
            'ban-tai' => 'pickup',
            'hatchback' => 'hatch',
            'xe-dien' => 'ev',
            default => 'sedan',
        };
    }
}
