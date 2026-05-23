<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            ['id' => 1, 'name' => 'Cash', 'gateway_type' => null],
            ['id' => 2, 'name' => 'Credit Card', 'gateway_type' => 'card'],
            ['id' => 3, 'name' => 'QR Code', 'gateway_type' => 'qrph'],
            ['id' => 4, 'name' => 'Maya', 'gateway_type' => 'paymaya'],
            ['id' => 5, 'name' => 'BillEase', 'gateway_type' => 'billease'],
            ['id' => 6, 'name' => 'GrabPay', 'gateway_type' => 'grab_pay'],
            ['id' => 7, 'name' => 'DOB', 'gateway_type' => 'dob'],
            ['id' => 8, 'name' => 'Wallet', 'gateway_type' => 'wallet'],
        ]);
    }
}
