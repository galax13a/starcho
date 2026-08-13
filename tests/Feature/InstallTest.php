<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;

test('installer is hidden unless explicitly enabled', function () {
    config(['starcho.install_enabled' => false]);

    $this->get(route('install.index'))->assertNotFound();
});

test('installer exposes system checks when enabled', function () {
    config(['starcho.install_enabled' => true]);

    $this->get(route('install.index'))
        ->assertOk()
        ->assertSee('Instalar Starcho')
        ->assertSee('sin usar credenciales por defecto');
});

test('installer validates administrator credentials before changing the database', function () {
    config(['starcho.install_enabled' => true]);

    $this->from(route('install.index'))
        ->post(route('install.store'), [
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertRedirect(route('install.index'))
        ->assertSessionHasErrors('password');

    expect(Schema::hasTable('users'))->toBeTrue()
        ->and(User::query()->count())->toBe(0);
});
