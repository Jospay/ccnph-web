<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Service;
use App\Models\Shop;
use App\Models\Status;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            UserTypeSeeder::class,
            ServiceSeeder::class,
            BusinessTrainingSeeder::class,
            StatusSeeder::class,
            PaymentMethodSeeder::class,
            LoanSettingSeeder::class,
            MembershipSettingSeeder::class,
            ShareCapitalSettingSeeder::class,
            CategorySeeder::class,
            CooperativeSeeder::class,
            TermsAndConditionSeeder::class,
            AttributeSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user_type_id' => UserType::SUPER_ADMIN,
        ]);

        User::factory()->create([
            'name' => 'Member One',
            'email' => 'member1@example.com',
            'user_type_id' => UserType::MEMBER,
        ]);

        User::factory()->create([
            'name' => 'Member Two',
            'email' => 'member2@example.com',
            'user_type_id' => UserType::MEMBER,
        ]);

        User::factory()->create([
            'name' => 'Member Three',
            'email' => 'member3@example.com',
            'user_type_id' => UserType::MEMBER,
        ]);

        $seller = User::factory()->create([
            'name' => 'Member Seller',
            'email' => 'seller1@example.com',
            'user_type_id' => UserType::MEMBER,
            'is_seller' => true,
        ]);

        if ($seller) {
            Shop::factory()->create([
                'user_id' => $seller->id,
            ]);
        }

        Product::factory(20)->create();

        $this->call([
            ShareCapitalSeeder::class,
            DiminishingLoanSeeder::class,
            IntellectualPropertySeeder::class,
        ]);

        User::factory()->count(5)->create([
            'status_id' => Status::FOR_APPROVAL,
        ]);

        // Create admin for each service
        $services = Service::where('is_super_admin_only', false)->get();
        foreach ($services as $service) {
            $admin = User::factory()->create([
                'name' => $service->name.' Admin',
                'email' => Str::slug($service->name).'@example.com',
                'user_type_id' => UserType::ADMIN,
            ]);
            $admin->services()->attach($service->id);
        }
    }
}
