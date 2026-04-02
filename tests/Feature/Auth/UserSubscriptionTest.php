<?php

use App\Models\User;

test('new users start with free subscription data', function () {
    $user = User::factory()->create();

    $user->refresh();

    expect($user->subscription_level)->toBe('free');
    expect($user->whatsapp_verified_at)->toBeNull();
    expect($user->subscriptions)->toHaveCount(1);
    expect($user->subscriptions->first()->level)->toBe('free');
    expect($user->subscriptions->first()->is_active)->toBeTrue();
});