<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Lectura/escritura de cache tolerante a payloads no deserializables.
 *
 * Contexto (Laravel 13): `config/cache.php` trae `'serializable_classes' => false`,
 * de modo que los stores deserializan con `unserialize($value, ['allowed_classes' => false])`.
 * Ningun objeto se restaura: vuelve como `__PHP_Incomplete_Class`. Cualquier entrada
 * escrita por codigo anterior (Collections, modelos Eloquent, Carbon, DTOs) queda
 * "envenenada" y revienta al leerse, tipicamente en una vista Blade que llama
 * `->isNotEmpty()` sobre lo que creia una Collection.
 *
 * Mantenemos el default seguro (`serializable_classes => false`) y en su lugar:
 * 1. Solo guardamos arrays y escalares (`putPlain()` lo verifica en escritura).
 * 2. Validamos lo leido (`isPlain()`); si no es un payload plano se descarta,
 *    se recalcula y se reescribe, para que las entradas viejas se curen solas
 *    sin depender de `php artisan cache:clear`.
 */
class SafeCache
{
    /**
     * Equivalente a Cache::remember() pero limitado a valores planos.
     *
     * Si la entrada existente no es plana (objeto incompleto, Collection, modelo,
     * Carbon...) se olvida, se vuelve a ejecutar $callback y se reescribe.
     *
     * @template TValue of array|scalar|null
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public static function rememberPlain(string $key, \DateTimeInterface|\DateInterval|int|null $ttl, Closure $callback, ?string $store = null): mixed
    {
        $repository = static::store($store);

        $cached = $repository->get($key);

        if ($cached !== null && static::isPlain($cached)) {
            return $cached;
        }

        if ($cached !== null) {
            // Entrada envenenada o con formato no esperado: se descarta.
            $repository->forget($key);
        }

        $fresh = $callback();

        if (static::isPlain($fresh)) {
            $repository->put($key, $fresh, $ttl);
        }

        return $fresh;
    }

    /**
     * Lee una clave y devuelve $default si el payload no es plano.
     * Las entradas invalidas se olvidan para que se regeneren limpias.
     */
    public static function getPlain(string $key, mixed $default = null, ?string $store = null): mixed
    {
        $repository = static::store($store);

        $cached = $repository->get($key);

        if ($cached === null) {
            return $default;
        }

        if (static::isPlain($cached)) {
            return $cached;
        }

        $repository->forget($key);

        return $default;
    }

    /**
     * Lee una clave esperando un array plano.
     *
     * @return array<array-key, mixed>
     */
    public static function getPlainArray(string $key, array $default = [], ?string $store = null): array
    {
        $value = static::getPlain($key, $default, $store);

        return is_array($value) ? $value : $default;
    }

    /**
     * Escribe solo si el valor es plano. Devuelve false si se rechazo.
     */
    public static function putPlain(string $key, mixed $value, \DateTimeInterface|\DateInterval|int|null $ttl = null, ?string $store = null): bool
    {
        if (! static::isPlain($value)) {
            return false;
        }

        return static::store($store)->put($key, $value, $ttl);
    }

    /**
     * Un payload es "plano" (cacheable) si solo contiene arrays y escalares,
     * en cualquier nivel de anidamiento. Cualquier objeto lo invalida, incluidos
     * los `__PHP_Incomplete_Class` que devuelve el store cuando la entrada vieja
     * guardaba Collections o modelos Eloquent.
     */
    public static function isPlain(mixed $value, int $depth = 0): bool
    {
        if ($depth > 16) {
            return false;
        }

        if ($value === null || is_scalar($value)) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if (! static::isPlain($item, $depth + 1)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Aplana Collections/Arrayables anidados para poder cachearlos.
     * Los objetos que no se pueden aplanar se descartan (se convierten en null).
     */
    public static function plain(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 16) {
            return null;
        }

        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \__PHP_Incomplete_Class) {
            // Restos de una entrada envenenada: no hay nada que rescatar.
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            $value = $value->toArray();
        } elseif ($value instanceof \JsonSerializable) {
            $value = $value->jsonSerialize();
        } elseif (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (! is_array($value)) {
            return is_scalar($value) ? $value : null;
        }

        $out = [];

        foreach ($value as $key => $item) {
            $out[$key] = static::plain($item, $depth + 1);
        }

        return $out;
    }

    private static function store(?string $store): Repository
    {
        return Cache::store($store);
    }
}
