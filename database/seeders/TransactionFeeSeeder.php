<?php

namespace Database\Seeders;

use App\Models\TransactionFee;
use Illuminate\Database\Seeder;

class TransactionFeeSeeder extends Seeder
{
    public function run(): void
    {
        // Transfer transaction fee
        TransactionFee::updateOrCreate(
            ['module' => 'Transfer'],
            [
                'type' => TransactionFee::TYPE_PHP,
                'transfer_fee' => 0,
                'minimum_fee' => 1,
            ],
        );

        // Load transaction fee - 2%
        TransactionFee::updateOrCreate(
            ['module' => 'Load'],
            [
                'type' => TransactionFee::TYPE_PERCENTAGE,
                'transfer_fee' => 2,
                'minimum_fee' => 1,
            ],
        );

        // Membership transaction fee - 2%
        TransactionFee::updateOrCreate(
            ['module' => 'Membership'],
            [
                'type' => TransactionFee::TYPE_PERCENTAGE,
                'transfer_fee' => 2,
                'minimum_fee' => 0,
            ],
        );
    }
}
