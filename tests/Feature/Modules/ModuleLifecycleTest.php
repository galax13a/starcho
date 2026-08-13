<?php

use App\Livewire\Admin\MenuBuilder;
use App\Livewire\Admin\ModulesManager;
use App\Models\StarchoMenuItem;
use App\Models\StarchoModule;
use App\Models\User;
use Livewire\Livewire;

test('module and menu managers reject users without admin authorization', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ModulesManager::class)->assertForbidden();
    Livewire::test(MenuBuilder::class)->assertForbidden();
});

test('module lifecycle is atomic and keeps active state consistent', function () {
    $module = StarchoModule::create([
        'key' => 'test-module',
        'name' => ['en' => 'Test module'],
        'description' => ['en' => 'Module used by the lifecycle test'],
        'installed' => false,
        'active' => false,
        'config' => [
            'menu_items' => [
                [
                    'panel' => 'app',
                    'name' => ['en' => 'Test module'],
                    'route' => 'app.dashboard',
                    'url' => 'javascript:alert(1)',
                    'target' => 'invalid',
                ],
            ],
        ],
    ]);

    expect(StarchoModule::isActive($module->key))->toBeFalse();

    $module->install();
    $item = StarchoMenuItem::where('module_key', $module->key)->firstOrFail();

    expect($module->fresh()->installed)->toBeTrue()
        ->and($module->fresh()->active)->toBeTrue()
        ->and($item->active)->toBeTrue()
        ->and($item->url)->toBeNull()
        ->and($item->target)->toBe('_self')
        ->and(StarchoModule::isActive($module->key))->toBeTrue();

    $module->deactivate();
    expect($module->fresh()->active)->toBeFalse()
        ->and($item->fresh()->active)->toBeFalse()
        ->and(StarchoModule::isActive($module->key))->toBeFalse();

    $module->uninstall();
    expect($module->fresh()->installed)->toBeFalse()
        ->and($module->fresh()->active)->toBeFalse()
        ->and(StarchoMenuItem::where('module_key', $module->key)->exists())->toBeFalse();
});
