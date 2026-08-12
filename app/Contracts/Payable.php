<?php

namespace App\Contracts;

use App\Models\Payment;

interface Payable
{
    public function onPaymentSuccess(Payment $payment): void;

    public function onPaymentFailed(Payment $payment): void;

    public function cooperativeServiceSlug(): ?string;
}
