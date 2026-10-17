<?php

namespace App\Services\Store;

use App\Models\CooperativeDiscount;
use App\Models\Status;
use App\Models\TransactionFee;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Collection;

/**
 * Single place for store pricing, used by the checkout screen (preview) and by
 * CheckoutService (real order), so both always show/charge the same numbers.
 *
 * - Membership discount      : active member (user_type_id 3, status_id 1)
 * - Shared Capital discount  : active member that already has shared capital
 * - Point discount           : only when paying with Points
 * - Online processing fee    : TransactionFee module "Store", online payments only
 *
 * Discounts are computed on the merchandise subtotal of each shop order
 * (shipping is not discounted).
 */
class CheckoutPricingService
{
    public const SHIPPING_FEE = 60;

    public const FEE_MODULE = 'Store';

    /**
     * true  = a member with shared capital gets Membership + Shared Capital
     * false = a member with shared capital gets only the Shared Capital discount
     */
    public const STACK_SHARED_CAPITAL = true;

    public function isActiveMember(User $user): bool
    {
        return (int) $user->user_type_id === UserType::MEMBER
            && (int) $user->status_id === Status::ACTIVE;
    }

    public function hasSharedCapital(User $user): bool
    {
        $shareCapital = $user->shareCapital;

        return $shareCapital !== null && $shareCapital->isFullyPaid();
    }

    /**
     * Discounts for ONE shop order.
     *
     * @return array{membership: float, shared_capital: float, point: float, total: float}
     */
    public function forStore(User $user, float $subtotal, bool $usingPoints): array
    {
        $membership = 0.0;
        $sharedCapital = 0.0;
        $point = 0.0;

        if ($this->isActiveMember($user)) {
            $hasSharedCapital = $this->hasSharedCapital($user);

            if ($hasSharedCapital) {
                $sharedCapital = $this->discountFor(CooperativeDiscount::MODULE_SHARED_CAPITAL, $subtotal);
            }

            if (! $hasSharedCapital || self::STACK_SHARED_CAPITAL) {
                $membership = $this->discountFor(CooperativeDiscount::MODULE_MEMBERSHIP, $subtotal);
            }
        }

        if ($usingPoints) {
            $point = $this->discountFor(CooperativeDiscount::MODULE_POINT, $subtotal);
        }

        // never discount more than the merchandise subtotal
        $membership = min($membership, $subtotal);
        $sharedCapital = min($sharedCapital, round($subtotal - $membership, 2));
        $point = min($point, round($subtotal - $membership - $sharedCapital, 2));

        return [
            'membership' => round($membership, 2),
            'shared_capital' => round($sharedCapital, 2),
            'point' => round($point, 2),
            'total' => round($membership + $sharedCapital + $point, 2),
        ];
    }

    /**
     * Flat/percentage processing fee for online payments (TransactionFee "Store").
     * Charged once per checkout. minimum_fee is intentionally ignored.
     */
    public function onlineFee(float $amount): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        return TransactionFee::forModule(self::FEE_MODULE)?->calculate($amount) ?? 0.0;
    }

    /**
     * Numbers for the checkout screen. Items must have productVariant.product loaded.
     * The app picks which extras apply from the selected payment method:
     *   COD    -> total
     *   Points -> total - point_discount
     *   Online -> total + online_fee
     */
    public function preview(User $user, Collection $items): array
    {
        $subtotal = 0.0;
        $shipping = 0.0;
        $membership = 0.0;
        $sharedCapital = 0.0;
        $point = 0.0;

        $items
            ->groupBy(fn ($item) => $item->productVariant->product->store_id)
            ->each(function ($storeItems) use ($user, &$subtotal, &$shipping, &$membership, &$sharedCapital, &$point) {
                $storeSubtotal = (float) $storeItems->sum(
                    fn ($item) => $item->quantity * $item->productVariant->price
                );

                $base = $this->forStore($user, $storeSubtotal, false);
                $withPoints = $this->forStore($user, $storeSubtotal, true);

                $subtotal += $storeSubtotal;
                $shipping += self::SHIPPING_FEE;
                $membership += $base['membership'];
                $sharedCapital += $base['shared_capital'];
                $point += $withPoints['point'];
            });

        $total = round($subtotal + $shipping - $membership - $sharedCapital, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'shipping_fee' => round($shipping, 2),
            'membership_discount' => round($membership, 2),
            'shared_capital_discount' => round($sharedCapital, 2),
            'point_discount' => round($point, 2),
            // discounts that always apply (Points discount depends on the payment method)
            'discount' => round($membership + $sharedCapital, 2),
            'total' => $total,
            'online_fee' => $this->onlineFee($total),
        ];
    }

    private function discountFor(string $module, float $subtotal): float
    {
        return CooperativeDiscount::forModule($module)?->calculate($subtotal) ?? 0.0;
    }
}
