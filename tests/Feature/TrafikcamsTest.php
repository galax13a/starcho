<?php

use App\Models\User;

test('guests are redirected before reaching the Trafikcams console', function () {
    $this->get(route('app.trafikcams'))
        ->assertRedirect(route('login'));
});

test('authenticated users can open the Trafikcams console', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('app.trafikcams'))
        ->assertOk()
        ->assertSee('TRAFIKCAMS')
        ->assertSee('V1')
        ->assertSee(__('trafikcams.connect'));
});
