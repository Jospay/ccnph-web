<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminOnly = [
            'Coop Membership',
        ];

        $services = [
            'Coop Membership',
            'Business Training',
            'Intellectual Property Assistance',
            'Loan Assistance',
            // 'Funding & Invest Opportunities',
            // 'Licensing & Permit Assistance',
            // 'R & D Collaboration',
            // 'Ask an Expert Assistance',
            // 'FISMPC Online Store',
            // 'Product Validation Services',
            // 'Lost & Found',
            // 'Suggest Service',
            // 'News & Events',
        ];

        foreach ($services as $service) {
            DB::table('services')->insert([
                'name' => $service,
                'slug' => Str::slug($service),
                'is_active' => true,
                'is_super_admin_only' => in_array($service, $superAdminOnly),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}