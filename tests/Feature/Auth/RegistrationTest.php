<?php

use Laravel\Fortify\Features;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    Storage::fake('local');

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        // Use a completely unique email so it NEVER clashes with your DatabaseSeeder
        'email' => 'admin123@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',

        'phone' => '09123456789',
        'gender' => 'Male',
        'birthdate' => '2000-01-01',
        'region' => 'Region III',
        'province' => 'Pampanga',
        'city' => 'Angeles City',
        'barangay' => 'Balibago',
        'street' => '123 Main St',
        'postal_code' => '2009',
        'valid_id_type' => 'Driver License',
        'valid_id_number' => '123456789',

        'front_valid_id_picture' => UploadedFile::fake()->image('front_id.jpg'),
        'back_valid_id_picture' => UploadedFile::fake()->image('back_id.jpg'),
    ]);

    $this->assertAuthenticated();

    // Change 'home' to whatever route name users are actually sent to after logging in!
    $response->assertRedirect('/dashboard');
});
