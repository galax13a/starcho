# Analisis estatico: Larastan / PHPStan

Starcho usa **Larastan 3** (la extension de Laravel para PHPStan 2) sobre PHP 8.4.

## Comandos

```bash
composer stan            # analiza app, routes, config, database
composer stan:baseline   # congela los errores actuales en phpstan-baseline.neon
composer stan:clear      # limpia la cache de resultados
composer test            # config:clear + pint --test + stan + pest
```

Instalacion (ya declarada en `composer.json`, solo hay que resolverla):

```bash
composer update larastan/larastan phpstan/extension-installer --with-all-dependencies
```

O desde cero, si prefieres el comando explicito:

```bash
composer require --dev larastan/larastan:^3.10 phpstan/extension-installer:^1.4
```

`phpstan/extension-installer` registra `larastan/extension.neon` por si solo, asi
que `phpstan.neon` no necesita el bloque `includes` de la extension.

---

## Los niveles, y cual te conviene

| Nivel | Que agrega | Coste en un proyecto Laravel existente |
| --- | --- | --- |
| 0 | Sintaxis, clases/metodos/funciones inexistentes | Trivial |
| 1 | Variables sin definir, propiedades y metodos magicos | Bajo |
| 2 | Tipos de retorno y propiedades en todas las expresiones | Bajo |
| 3 | Los mismos, validados a fondo | Bajo |
| 4 | **Codigo muerto**: condiciones siempre true/false, ramas inalcanzables, variables sin usar | Medio |
| 5 | **Tipos de los argumentos** que pasas a metodos y funciones | Medio |
| 6 | Exige tipado explicito: reporta cada tipo faltante | **Alto** |
| 7 | Union types parcialmente incorrectos | Medio |
| 8 | **Null-safety**: llamar metodos sobre valores nullable | **Alto** |
| 9 | Uso estricto de `mixed` | Medio |
| 10 | Tipado total | Alto |

**Los dos saltos caros en Laravel son el 6 y el 8.**

- El **6** obliga a anotar generics en todo el codebase: `Collection<int, Post>`,
  `Builder<Post>`, `HasMany<Comment, $this>`, `array<string, mixed>`. Es mucho
  PHPDoc mecanico, pero es lo que hace que el resto de niveles sean utiles: sin
  generics, PHPStan ve `Collection` y no sabe que hay dentro.
- El **8** saca a la luz cada `Model::find()`, `->first()`, `auth()->user()`,
  `config('x')` y `request()->user()` que puede devolver `null` y sobre el que
  llamas un metodo. Es donde vive el clasico *"Call to a member function on null"*.

Este proyecto arranca en **nivel 5** con baseline. Razon: es el ultimo nivel que
se alcanza sin una campana de anotaciones, y ya cubre lo que mas rompe en
produccion — argumentos con el tipo equivocado, metodos que no existen y codigo
muerto (nivel 4 va incluido).

### Plan de subida (ratchet)

La idea es que **el CI nunca este rojo** y que la deuda solo pueda bajar.

1. `composer stan` → mira cuantos errores hay de verdad.
2. `composer stan:baseline` → congela esa deuda. CI en verde.
3. Trabaja normal. Cualquier error **nuevo** falla el pipeline, porque no esta
   en el baseline.
4. Cuando el baseline lleve semanas sin crecer, sube `level: 6` en
   `phpstan.neon`, regenera el baseline y repite.
5. En el nivel 7, activa `checkModelProperties: true` (ver abajo).
6. Nivel 8. Cuando el baseline quede vacio, borra `phpstan-baseline.neon` y su
   `includes`.

Regla de oro para revisiones: **un PR nunca agrega lineas al baseline.** Si las
agrega, el error se arregla, no se congela.

---

## Que entiende Larastan sin configuracion

No hace falta configurar nada de esto, la extension ya lo modela:

- **Modelos Eloquent**: atributos desde `$casts`, `$fillable`, `@property` y las migraciones.
- **Relaciones**: `HasMany`, `BelongsTo`, `MorphMany`... con su tipo de retorno.
- **Facades**: `Cache::get()`, `DB::table()`, `Schema::hasTable()` resueltos al servicio real.
- **Collections**: generics `Collection<TKey, TValue>` y el tipo de retorno de `map`, `filter`, `pluck`.
- **Helpers**: `collect()`, `config()`, `request()`, `auth()`, `now()`, `route()`.
- **Scopes**: `Post::published()` si esta declarado como `scopePublished()`.
- **Factories** y **Enums** nativos de PHP 8.1+.

### `checkModelProperties`

Esta apagado a proposito en `phpstan.neon`. Cuando esta en `true`, PHPStan
verifica que cada `$model->columna` exista realmente. Es la opcion mas valiosa de
Larastan **y** la mayor fuente de falsos positivos si los modelos no declaran sus
columnas. Actívala cuando los modelos tengan bloques `@property`:

```php
/**
 * @property int $id
 * @property string $title
 * @property string|null $excerpt
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 */
class Post extends Model {}
```

Generar esos bloques a mano es tedioso; `barryvdh/laravel-ide-helper`
(`php artisan ide-helper:models -M`) los escribe por ti a partir del esquema.

---

## Errores que vas a ver, y como se arreglan

### `missingType.generics` — *Method X() return type with generic class Collection does not specify its types*

**Que significa:** devuelves una `Collection` pero no dices de que.
**Por que ocurre:** `Collection` es generica; sin parametros PHPStan asume `Collection<mixed, mixed>` y pierde el rastro de los tipos aguas abajo.

```php
// antes
public static function activeWithUrl(): Collection

// despues
/** @return \Illuminate\Support\Collection<int, self> */
public static function activeWithUrl(): Collection
```

### `missingType.iterableValue` — *has parameter $x with no value type specified in iterable type array*

Igual que el anterior, para arrays: `array` → `array<string, mixed>`,
`list<int>`, `array{key: string, count: int}` (shape) segun el caso.

### `argument.type` (nivel 5) — *Parameter #1 $id of method find() expects int, mixed given*

**Que significa:** el valor que pasas puede no ser del tipo que el metodo pide.
**Por que ocurre:** tipicamente el valor viene de `request()`, de un cache o de un
array sin tipar, y PHPStan solo ve `mixed`.
**Como se arregla:** valida o estrecha el tipo antes de pasarlo. No con un cast a
ciegas — el cast oculta el problema:

```php
// mal: silencia a PHPStan pero un array se convierte en 1
$post = Post::find((int) $id);

// bien: estrechar de verdad
if (! is_int($id) && ! is_string($id)) {
    return null;
}
$post = Post::find($id);
```

### `deadCode.unreachable` / `if.alwaysTrue` (nivel 4)

**Que significa:** esa rama no se puede ejecutar nunca.
**Por que ocurre:** casi siempre una comprobacion redundante que quedo de un
refactor (`if ($model !== null)` cuando el tipo ya es no-nullable), o un `return`
antes de mas codigo.
**Como se arregla:** borrar la rama. Si crees que si es alcanzable, entonces la
firma de tipos esta mintiendo y hay que corregirla.

### `nullsafe` / `method.nonObject` (nivel 8) — *Cannot call method on X|null*

```php
// antes
$name = Post::find($id)->title;

// opcion A: fallar explicito
$post = Post::findOrFail($id);
$name = $post->title;

// opcion B: manejar el null
$name = Post::find($id)?->title ?? 'Sin titulo';
```

Para `auth()->user()` en rutas ya protegidas por middleware, la forma limpia no es
un `@phpstan-ignore` sino afirmar el tipo:

```php
$user = type(auth()->user())->asInstanceOf(User::class); // via webmozart/assert o similar
// o simplemente
$user = $request->user();
assert($user instanceof User);
```

### `larastan.noEnvCallsOutsideOfConfig`

**Que significa:** hay un `env()` fuera de `config/`.
**Por que importa:** con `php artisan config:cache` (obligatorio en produccion)
`env()` devuelve `null`. Es un bug real, no un tema de estilo.
**Como se arregla:** mueve el `env()` a un archivo de `config/` y lee `config('...')`
desde el codigo.

---

## Buenas practicas de tipado para este proyecto

**Return types y parameter types siempre.** PHP los verifica en runtime; PHPDoc no.
El PHPDoc es para lo que el motor de tipos de PHP no sabe expresar (generics, shapes,
literales).

```php
// PHP verifica esto en runtime
public function stats(): array

// esto solo lo verifica PHPStan, pero es lo que le da informacion util
/** @return array{total_keys: int, post_keys: int, last_cleared_at: string|null} */
public function stats(): array
```

**Typed properties** en lugar de `@var`:

```php
// antes
/** @var string */
private $prefix;

// despues
private string $prefix;
```

**Array shapes** para payloads estructurados (config, respuestas de API, payloads
de cache). Convierten un `array` opaco en algo que PHPStan puede verificar campo
a campo.

**`list<T>` en vez de `array<int, T>`** cuando las claves son 0,1,2... consecutivas
(lo que devuelve `->values()->all()` o `->pluck()->all()`). Es mas preciso.

**Enums nativos en lugar de constantes string.** Un `enum PostStatus: string` le da
a PHPStan un conjunto cerrado de valores; `const STATUS = ['draft' => ...]` no.

**Nunca silencies un error sin explicar por que.** Si de verdad es un falso
positivo:

```php
/** @phpstan-ignore-next-line Livewire resuelve $this->form en runtime */
```

Un `ignoreErrors` sin comentario en `phpstan.neon` se convierte en deuda invisible.

---

## Codigo muerto e imports innecesarios

PHPStan cubre parte de esto (nivel 4: ramas inalcanzables, variables sin usar;
nivel 0: metodos inexistentes), pero **no detecta imports `use` sin usar ni
metodos privados nunca llamados de forma fiable**. Para eso:

- **Imports sin usar**: Pint ya los quita. El preset `laravel` incluye la regla
  `no_unused_imports`. `composer lint` los limpia.
- **Metodos/propiedades privadas sin usar**: requiere las reglas extra de
  `tomasvotruba/unused-public` o `phpstan/phpstan-deprecation-rules`. Se pueden
  agregar mas adelante.
- **Clases enteras sin usar**: ninguna herramienta estatica lo resuelve bien en
  Laravel, porque el contenedor las resuelve por string. Revision manual.

---

## Referencias

- [Larastan](https://larastan.org/)
- [PHPStan: niveles de analisis](https://phpstan.org/user-guide/rule-levels)
- [PHPStan: baseline](https://phpstan.org/user-guide/baseline)
