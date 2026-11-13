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
            'CCNPH Online Store',
            'News & Events',
            // 'Intellectual Property Assistance',
            // 'Loan Assistance',
            // 'Funding & Invest Opportunities',
            // 'Licensing & Permit Assistance',
            // 'R & D Collaboration',
            // 'Ask an Expert Assistance',
            // 'Product Validation Services',
            // 'Lost & Found',
            // 'Suggest Service',
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['name' => $service],
                [
                    'slug' => Str::slug($service),
                    'is_active' => true,
                    'is_super_admin_only' => in_array($service, $superAdminOnly),
                    'is_active_transparency' => false,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
