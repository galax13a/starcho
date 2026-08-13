<?php

namespace App\Models\Concerns;

use Closure;

/**
 * Memoiza resultados durante la vida de un request.
 *
 * Motivacion: los modelos de configuracion (SiteSetting, ContentSetting,
 * SiteLanguage) se consultan desde los Blade y desde middleware, y una sola
 * pagina puede llamar a `cached()` veinte veces. Aunque el id venga de la cache,
 * cada llamada repetia `Schema::hasTable()` — que en MySQL golpea
 * `information_schema` — mas un `find()`. Con este trait ese trabajo ocurre una
 * vez por request.
 *
 * Nota sobre el ciclo de vida: las propiedades estaticas de un trait son
 * independientes por cada clase que lo usa, y en PHP-FPM mueren al terminar el
 * request. Para tests y para runtimes persistentes (Octane), `flushMemo()` se
 * invoca desde `AppServiceProvider::boot()`, que corre una vez por instancia de
 * la aplicacion, y desde los hooks `saved`/`deleted` de cada modelo.
 */
trait MemoizesPerRequest
{
    /**
     * @var array<string, mixed>
     */
    private static array $memo = [];

    /**
     * Devuelve el valor memoizado para $key, resolviendolo la primera vez.
     *
     * Se usa `array_key_exists` en lugar de `isset` para que un `null`
     * legitimamente resuelto tambien quede memoizado y no se recalcule.
     *
     * @template TValue
     *
     * @param  Closure(): TValue  $resolver
     * @return TValue
     */
    protected static function memo(string $key, Closure $resolver): mixed
    {
        if (array_key_exists($key, static::$memo)) {
            return static::$memo[$key];
        }

        return static::$memo[$key] = $resolver();
    }

    /**
     * Invalida la memoizacion de esta clase.
     */
    public static function flushMemo(): void
    {
        static::$memo = [];
    }
}
