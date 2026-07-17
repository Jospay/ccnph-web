<?php

use App\Models\User;
use App\Models\UserType; 

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create([
        'user_type_id' => UserType::ADMIN, 
    ]);
    
    $this->actingAs($user);

    $response = $this->get(route('dashboard.index'));
    $response->assertOk();
});