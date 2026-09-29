<?php

namespace Database\Seeders;

use App\Models\TransactionChannel;
use Illuminate\Database\Seeder;

class TransactionChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            [
                'code' => 'instapay',
                'name' => 'InstaPay (QR Ph)',
                'search' => 'InstaPay',
                'provider' => 'instapay',
                'category' => 'Payment Network',
            ],
            [
                'code' => 'gcash',
                'name' => 'GCash',
                'search' => 'G-Xchange',
                'provider' => 'instapay',
                'category' => 'E-Wallet',
            ],
            [
                'code' => 'maya',
                'name' => 'Maya',
                'search' => 'Maya Philippines',
                'provider' => 'instapay',
                'category' => 'E-Wallet',
            ],
            [
                'code' => 'aub',
                'name' => 'AUB',
                'search' => 'Asia United Bank',
                'provider' => 'instapay',
                'category' => 'Bank',
            ],
            [
                'code' => 'bdo',
                'name' => 'BDO Unibank',
                'search' => 'BDO Unibank',
                'provider' => 'instapay',
                'category' => 'Bank',
            ],
            [
                'code' => 'bpi',
                'name' => 'BPI',
                'search' => 'Bank of the Philippine Islands',
                'provider' => 'instapay',
                'category' => 'Bank',
            ],
            [
                'code' => 'landbank',
                'name' => 'LandBank',
                'search' => 'Land Bank of the Philippines',
                'provider' => 'instapay',
                'category' => 'Bank',
            ],
            [
                'code' => 'metrobank',
                'name' => 'Metrobank',
                'search' => 'Metropolitan Bank',
                'provider' => 'instapay',
                'category' => 'Bank',
            ],
            [
                'code' => 'unionbank',
                'name' => 'UnionBank',
                'search' => 'Union Bank of the Philippines',
                'provider' => 'instapay',
                'category' => 'Bank',
            ],
        ];

        foreach ($channels as $channel) {
            TransactionChannel::updateOrCreate(
                ['code' => $channel['code']],
                [
                    ...$channel,
                    'is_active' => true,
                ]
            );
        }
    }
}
