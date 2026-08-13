<?php

use App\Support\SafeCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

// SafeCache usa el facade Cache, asi que este archivo necesita la app booteada.
uses(TestCase::class);

/**
 * Reproduce lo que devuelve un cache store de Laravel 13 cuando la entrada fue
 * escrita con un objeto y se deserializa con allowed_classes => false
 * (config/cache.php => 'serializable_classes' => false).
 */
function starchoIncompleteObject(): object
{
    return unserialize(serialize(new stdClass), ['allowed_classes' => false]);
}

it('detecta payloads planos', function () {
    expect(SafeCache::isPlain(null))->toBeTrue()
        ->and(SafeCache::isPlain('html'))->toBeTrue()
        ->and(SafeCache::isPlain(42))->toBeTrue()
        ->and(SafeCache::isPlain(false))->toBeTrue()
        ->and(SafeCache::isPlain([1, 2, ['a' => 'b']]))->toBeTrue();
});

it('rechaza payloads con objetos, incluso anidados', function () {
    expect(SafeCache::isPlain(collect([1, 2])))->toBeFalse()
        ->and(SafeCache::isPlain(new DateTimeImmutable))->toBeFalse()
        ->and(SafeCache::isPlain(starchoIncompleteObject()))->toBeFalse()
        ->and(SafeCache::isPlain(['ok' => 1, 'bad' => collect([1])]))->toBeFalse()
        ->and(SafeCache::isPlain([['deep' => [starchoIncompleteObject()]]]))->toBeFalse();
});

it('descarta y recalcula una entrada envenenada', function () {
    Cache::put('starcho.test.poisoned', starchoIncompleteObject(), 3600);

    $value = SafeCache::rememberPlain('starcho.test.poisoned', 3600, fn () => ['es', 'en']);

    // Se ignoro el objeto incompleto y se recalculo.
    expect($value)->toBe(['es', 'en']);

    // Y la entrada quedo curada: la siguiente lectura ya no ejecuta el callback.
    expect(SafeCache::rememberPlain('starcho.test.poisoned', 3600, fn () => ['nunca']))
        ->toBe(['es', 'en']);
});

it('no vuelve a ejecutar el callback si la entrada es valida', function () {
    $calls = 0;

    $callback = function () use (&$calls) {
        $calls++;

        return ['a'];
    };

    SafeCache::rememberPlain('starcho.test.valid', 3600, $callback);
    SafeCache::rememberPlain('starcho.test.valid', 3600, $callback);

    expect($calls)->toBe(1);
});

it('no persiste valores no planos', function () {
    $stored = SafeCache::putPlain('starcho.test.reject', collect([1, 2]), 3600);

    expect($stored)->toBeFalse()
        ->and(Cache::has('starcho.test.reject'))->toBeFalse();
});

it('getPlainArray devuelve el default y olvida la clave invalida', function () {
    Cache::put('starcho.test.index', starchoIncompleteObject(), 3600);

    expect(SafeCache::getPlainArray('starcho.test.index'))->toBe([])
        ->and(Cache::has('starcho.test.index'))->toBeFalse();
});

it('plain() aplana collections, modelos y fechas', function () {
    $flat = SafeCache::plain([
        'codes' => collect(['es', 'en']),
        'when' => new DateTimeImmutable('2026-01-02T03:04:05+00:00'),
        'nested' => ['inner' => collect([['id' => 1]])],
    ]);

    expect(SafeCache::isPlain($flat))->toBeTrue()
        ->and($flat['codes'])->toBe(['es', 'en'])
        ->and($flat['when'])->toBe('2026-01-02T03:04:05+00:00')
        ->and($flat['nested']['inner'][0]['id'])->toBe(1);
});
