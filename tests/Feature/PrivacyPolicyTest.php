<?php

use App\Models\PrivacyPolicy;
use Inertia\Testing\AssertableInertia as Assert;

test('privacy policy page renders the latest published policy', function () {
    PrivacyPolicy::query()->create([
        'name' => 'Previous Privacy Policy',
        'content' => '<p>Previous content</p>',
    ]);

    $policy = PrivacyPolicy::query()->create([
        'name' => 'Privacy Policy',
        'content' => '<p>Privacy policy content</p>',
    ]);

    $this->get(route('privacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Privacy/Index')
            ->where('policy.id', $policy->id)
            ->where('policy.name', 'Privacy Policy')
            ->where('policy.content', '<p>Privacy policy content</p>'));
});
