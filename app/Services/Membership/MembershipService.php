<?php

namespace App\Services\Membership;

use App\Models\MembershipSetting;
use App\Models\TransactionFee;

class MembershipService
{
    public const FEE_MODULE = 'Membership';

    public function getFeeConfig(): ?TransactionFee
    {
        return TransactionFee::forModule(self::FEE_MODULE);
    }

    public function calculateFee(float $amount): float
    {
        return $this->getFeeConfig()?->calculate($amount) ?? 0.00;
    }

    public function getSettings(): array
    {
        $setting = MembershipSetting::current();

        return [
            'share_capital_amount' => $setting->share_capital_amount,
            'payment_options' => collect($setting->allowed_term_months)
                ->map(fn ($months) => [
                    'term_months' => $months,
                    'label' => $months === 1
                        ? 'Pay in Full'
                        : "{$months} Monthly Installments",
                    'amount_per_term' => (int) ceil($setting->share_capital_amount / $months),
                ]),
        ];
    }
}
