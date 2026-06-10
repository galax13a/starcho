# Starcho

Starter kit modular para construir aplicaciones con **Laravel 13, Livewire 4, Flux UI v2, PowerGrid y Tailwind CSS v4**.

Starcho viene con dos superficies claras:

- `/admin`: panel de administracion trabajado sobre **Flux UI**, Livewire y componentes del Star Kit.
- `/app`: area privada de usuario, modular, con menu dinamico y componentes reutilizables de Starcho.

La regla principal del proyecto es simple: cada nueva funcionalidad debe apoyarse en Laravel, Livewire, Flux UI cuando este en admin, y en las librerias/componentes propios de Starcho antes de inventar UI o servicios nuevos.

---

## Stack

| Capa | Tecnologia |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+ |
| UI reactiva | Livewire 4 + Alpine.js |
| Admin UI | Flux UI v2 |
| Tablas | PowerGrid v6 |
| CSS | Tailwind CSS v4 + Vite |
| Auth | Laravel Fortify, email verification, 2FA |
| Roles/permisos | Spatie Laravel Permission |
| Traducciones de modelos | Spatie Laravel Translatable |
| Storage | Local, Amazon S3, DigitalOcean Spaces, Cloudflare R2 |
| AI texto | Laravel AI con OpenAI, DeepSeek, Anthropic y OpenRouter |
| AI media | OpenAI Images, fal.ai y Replicate para imagen/video |
| AI billing | Planes, cuotas, costos y markup con `AiPlan`, `AiAssetGeneration` y `config/ai_pricing.php` |
| Graficas | ApexCharts via `x-starcho-chart` |
| JS | `resources/js/starcho.js` + bundles por area |

---

## Instalacion

```bash
git clone <repo>
cd starcho
composer install
npm install
php artisan starcho:install
npm run dev
```

Build de produccion:

```bash
npm run build
php artisan optimize
```

Credenciales iniciales:

| Campo | Valor |
| --- | --- |
| Email | `admin@starcho.com` |
| Password | `password` |
| Rol | `admin` |

---

## Rutas principales

### Publicas

| Ruta | Nombre | Uso |
| --- | --- | --- |
| `/` | `home` | Home publica desde `PageController::home` |
| `/language/{locale}` | `language.switch` | Cambia idioma |
| `/sitemap.xml` | `sitemap` | Sitemap XML |
| `/media/files/{media}` | `media.files.show` | Proxy/entrega de archivos y variantes |
| `/media/albums/{album:slug}` | `media.albums.show` | Album publico |
| `/media/albums/{album:slug}/unlock` | `media.albums.unlock` | Desbloqueo de album protegido |
| `/{locale}/blog` | `blog.index` | Blog publico localizado |
| `/{locale}/blog/{slug}` | `blog.show` | Post publico localizado |
| `/{locale}/{slug}` | `page.show` | Pagina CMS localizada |

### Admin

Todas las rutas admin viven bajo `/admin` y usan `auth`, `verified`, `role:root|admin` y `permission:view-admin`.

| Ruta | Nombre | Uso |
| --- | --- | --- |
| `/admin` | `admin.index` | Dashboard |
| `/admin/dashboard` | `admin.dashboard` | Dashboard |
| `/admin/site` | `admin.site.index` | Site Manager: branding, SEO, idiomas, social, storage y AI |
| `/admin/site` `PUT` | `admin.site.update` | Guardar configuracion del sitio |
| `/admin/site/ai` `PUT` | `admin.site.ai.update` | Guardar AI providers/modelos |
| `/admin/site/page-editor` | `admin.site.pages.edit` | Editor de paginas Folio |
| `/admin/ai` | `admin.ai.index` | AI Manager: texto, imagen, video, modelos, planes, costos y assets |
| `/admin/ai/featured-image` | `admin.ai.featured-image` | Generar imagen destacada con AI |
| `/admin/comments` | `admin.comments.index` | Comentarios editoriales de posts |
| `/admin/posts/comments` | `admin.posts.comments` | Comentarios editoriales desde el modulo posts |
| `/admin/storage` | `admin.storage.index` | Pantalla dedicada de storage |
| `/admin/storage` `PUT` | `admin.storage.update` | Guardar storage |
| `/admin/storage/link` | `admin.storage.link` | Crear/verificar `storage:link` |
| `/admin/storage/test` | `admin.storage.test` | Subida de prueba |
| `/admin/storage/test-delete` | `admin.storage.test-delete` | Borrar prueba |
| `/admin/storage/plans` | `admin.storage.plans.store` | Crear plan de storage |
| `/admin/storage/plans/{plan}` | `admin.storage.plans.update` | Actualizar plan |
| `/admin/media` | `admin.media.index` | Galeria multimedia |
| `/admin/media/picker` | `admin.media.picker` | Selector de media para formularios/editor |
| `/admin/media/upload` | `admin.media.upload` | Subir archivos |
| `/admin/media/variants/bulk` | `admin.media.variants.bulk` | Generar variantes en lote |
| `/admin/media/{media}/variants` | `admin.media.variants.generate` | Generar variantes de un archivo |
| `/admin/media/albums` | `admin.media.albums.index` | Albums multimedia |
| `/admin/posts` | `admin.posts.index` | Posts del blog |
| `/admin/posts/create` | `admin.posts.create` | Crear post |
| `/admin/posts/{post}/edit` | `admin.posts.edit` | Editar post |
| `/admin/posts/upload-image` | `admin.posts.upload-image` | Subida al editor |
| `/admin/pages` | `admin.pages.index` | Paginas CMS |
| `/admin/pages/create` | `admin.pages.create` | Crear pagina CMS |
| `/admin/pages/{post}/edit` | `admin.pages.edit` | Editar pagina CMS |
| `/admin/content/settings` | `admin.content.settings` | Contenido, sitemap y broken links |
| `/admin/modules` | `admin.modules.index` | Modulos instalables |
| `/admin/menu` | `admin.menu.index` | Constructor del menu de `/app` |
| `/admin/cache` | `admin.cache.index` | Limpieza/optimizacion de caches |
| `/admin/roles` | `admin.roles.index` | Roles |
| `/admin/permissions` | `admin.permissions.index` | Permisos |
| `/admin/users` | `admin.users.index` | Usuarios |
| `/admin/users-ban` | `admin.users-ban.index` | Baneos |
| `/admin/geolocations` | `admin.geolocations.index` | Geolocalizacion |
| `/admin/tasks` | `admin.tasks.index` | Tareas globales |
| `/admin/contacts` | `admin.contacts.index` | Contactos globales |
| `/admin/notes` | `admin.notes.index` | Notas globales |

### App

`/app` es el area de usuario autenticado/verificado. El menu se arma desde base de datos con `StarchoMenuItem` y los modulos se activan desde `/admin/modules`.

| Ruta | Uso |
| --- | --- |
| `/app` | Dashboard privado |
| `/app/tasks` | Tareas del usuario |
| `/app/contacts` | Contactos del usuario |
| `/app/notes` | Notas del usuario |

---

## Admin con Flux UI

El admin se debe construir primero con **Flux UI** y despues con componentes Starcho. El layout base esta en:

- `resources/views/layouts/admin/sidebar.blade.php`
- `resources/css/starcho-admin.css`
- `resources/js/admin.js`

Reglas de UI para `/admin`:

- Usar Flux para inputs, buttons, tabs, modals, cards, dropdowns y layouts administrativos.
- Reutilizar componentes `x-starcho-*` cuando ya exista un patron del Star Kit.
- No duplicar HTML de botones, badges, acciones CRUD, estados o tarjetas si existe componente.
- Prefijar estilos propios de admin con `.sa-` o variables `--sa-*`.
- Mantener textos en `lang/es`, `lang/en` y `lang/pt_BR` cuando sean UI reutilizable.

Componentes clave del Star Kit:

| Componente | Uso |
| --- | --- |
| `x-starcho-card-admin-stats` | KPI admin |
| `x-starcho-card-statsOne` | Stats compactas |
| `x-starcho-crud1` | Acciones edit/delete en tablas |
| `x-starcho-btn-view-table` | Toggle de columnas PowerGrid |
| `x-starcho-btn-excel` | Import/export Excel |
| `x-starcho-active` | Estado activo/inactivo |
| `x-starcho-active-switch` | Booleanos en formularios |
| `x-starcho-status` | Estados semanticos |
| `x-starcho-chart` | Graficas ApexCharts |
| `x-starcho-popup-standar` | Modal base |
| `x-starcho-popup-admin-import` | Modal de importacion admin |
| `x-starcho-noty` | Notificaciones |
| `x-starcho-alert` | Toasts |

---

## Storage

El manejo de archivos esta centralizado en:

- `app/Models/StorageSetting.php`
- `app/Services/StorageService.php`
- `app/Models/Media.php`
- `app/Http/Controllers/Admin/StorageSettingsController.php`
- `app/Http/Controllers/MediaFileController.php`

Drivers soportados:

| Driver | Disco runtime | Campos principales |
| --- | --- | --- |
| `local` | `public` | `local_folder`, `local_url` |
| `s3` | `starcho_s3` | key, secret, region, bucket, endpoint, url, folder |
| `do_spaces` | `starcho_do` | key, secret, region, bucket, endpoint, CDN url, folder |
| `r2` | `starcho_r2` | account id, key, secret, bucket, endpoint, public url, folder |

Flujo de subida:

1. El usuario sube un archivo desde media, post editor, album, avatar o galeria.
2. `StorageService::upload()` valida cuota del usuario.
3. Las imagenes se convierten a WebP cuando GD esta disponible.
4. Se guarda el archivo en el disco activo.
5. Se crea un registro `Media` con driver, disk, path, url, mime, size, width, height, contexto y owner.
6. Si las variantes estan activas, se generan copias WebP responsive.
7. Se incrementa `users.storage_used_bytes`.

Configuracion importante en `/admin/site`:

- Driver por defecto.
- Carpeta raiz por driver.
- URL local/canonica para que Herd, Valet o dominios custom no generen URLs con `localhost`.
- Activar/desactivar variantes.
- Tamanos de variantes.
- Variante usada como preview.
- Tamano de avatar.
- Planes de storage.

Variantes de imagen:

- Se guardan en `media.variants`.
- El peso total extra se guarda en `media.variants_size`.
- `Media::preview_url` usa el tamano configurado.
- `Media::variantUrl($size)` entrega la mejor variante disponible por la ruta `media.files.show`.
- `StorageService::generateImageVariants($media, force: true)` regenera variantes.

Entrega de archivos:

- Local: `{local_url}/storage/{path}`.
- S3/Spaces: URL publica del disco si existe.
- R2: `r2_public_url` si esta configurado; si no, URL temporal desde Laravel.
- Variantes: siempre pueden pasar por `/media/files/{media}?variant=240`.

Avatares:

- `StorageService::uploadProfileAvatar()` recorta y convierte a WebP cuadrado.
- Usa `avatar_size` de `StorageSetting`.
- Reemplaza el avatar anterior y ajusta cuota.

---

## Galeria multimedia

Modelos principales:

- `Media`
- `MediaAlbum`
- `MediaTag`
- `MediaComment`
- `MediaRating`
- `MediaFavorite`

Funcionalidades:

- Subida multiple.
- Albums privados/publicos con slug.
- Adjuntar/desadjuntar archivos.
- Ordenar archivos dentro de album.
- Comentarios y ratings en archivos o albums.
- Favoritos.
- Tags.
- Descarga.
- Generacion individual o masiva de variantes.
- Visor Livewire en `App\Livewire\Admin\MediaViewer`.

Rutas publicas de media:

- `/media/files/{media}`
- `/media/albums/{album:slug}`
- `/media/albums/{album:slug}/unlock`

Rutas admin de media:

- `/admin/media`
- `/admin/media/albums`
- `/admin/media/variants/bulk`
- `/admin/media/{media}/variants`
- `/admin/media/{media}/download`

---

## AI

La configuracion AI vive en:

- `app/Models/AiSetting.php`
- `app/Models/AiPlan.php`
- `app/Models/AiAssetGeneration.php`
- `app/Services/PageAiContentService.php`
- `app/Services/Ai/AiImageService.php`
- `app/Services/Ai/AiVideoService.php`
- `app/Services/Ai/AiReplicateService.php`
- `app/Services/Ai/AiQuotaService.php`
- `app/Services/Ai/AiPricing.php`
- `app/Jobs/GenerateAiImageJob.php`
- `app/Http/Controllers/Admin/AiSettingsController.php`
- `app/Livewire/Admin/AiManager.php`
- `app/Livewire/Admin/PageAiAssistant.php`
- `app/Livewire/Admin/PageAiCreator.php`
- `app/Livewire/Admin/PostAiCreator.php`
- `app/Livewire/Admin/SitePageSeoAi.php`
- `app/Livewire/Admin/PostInsights.php`
- `config/starcho_ai.php`
- `config/ai_pricing.php`

### Texto

| Provider | Campo de API key | Modelos base |
| --- | --- | --- |
| `openai` | `openai_api_key` | `gpt-5.4-nano`, `gpt-5.4-mini`, `gpt-5.4`, `gpt-5.4-pro` |
| `deepseek` | `deepseek_api_key` | `deepseek-chat`, `deepseek-reasoner` |
| `anthropic` | `anthropic_api_key` | `claude-sonnet-4-6`, `claude-haiku-4-5-20251001` |
| `openrouter` | `openrouter_api_key` | `openai/gpt-4o-mini`, `anthropic/claude-sonnet-4.6`, `google/gemini-2.0-flash-001`, `deepseek/deepseek-chat`, otros |

El catalogo de modelos de texto es editable desde base de datos mediante `AiSetting::model_settings`. El panel ofrece sugerencias y enlaces de documentacion por provider (`AiSetting::MODEL_DOCS`).

### Imagen y video

| Tipo | Provider | Campo de API key | Servicio | Modelos base |
| --- | --- | --- | --- | --- |
| Imagen | `openai` | `openai_api_key` | `AiImageService` | `gpt-image-1`, `dall-e-3` |
| Imagen | `fal` | `fal_api_key` | `AiVideoService::generateImage()` | Flux, Recraft, Stable Diffusion |
| Imagen | `replicate` | `replicate_api_key` | `AiReplicateService::generateImage()` | Flux, SDXL, Ideogram |
| Video | `fal` | `fal_api_key` | `AiVideoService::submit()` | Kling, Veo, MiniMax, Luma |
| Video | `replicate` | `replicate_api_key` | `AiReplicateService::submitVideo()` | Kling, Wan, Hunyuan, Luma |

Imagen soporta presets `tiktok`, `800x600`, `480x360` y `custom`. Para OpenAI, Starcho adapta la orientacion a los tamanos aceptados por el provider.

Video es asincrono: se crea un `AiAssetGeneration` en `processing`, el panel hace polling con `AiManager::pollAssets()`, descarga el resultado cuando termina y lo guarda en la galeria como `Media`.

### Runtime y jobs

`config/starcho_ai.php` controla:

- `request_timeout`: segundos maximos de espera para AI sync.
- `async_threshold`: umbral para mandar trabajo a background.
- `time_limit_buffer`: margen para `set_time_limit()`.

`GenerateAiImageJob` permite generar imagenes en background. El job llama al servicio correcto segun provider y el panel notifica cuando el asset queda listo.

### Seguridad y secretos

- Las API keys se guardan con cast `encrypted`.
- `AiSetting::singleton()` mantiene una sola configuracion.
- AI debe estar habilitado y tener key del provider activo.
- Los modelos de texto, imagen y video se pueden activar/desactivar desde `model_settings`, `image_model_settings` y `video_model_settings`.
- Los usuarios nuevos reciben automaticamente el plan AI gratuito activo si existe.

### Planes, cuotas y costos

`AiPlan` define:

- `text_token_quota`
- `image_quota`
- `video_quota`
- `monthly_budget_cents`
- `monthly_price`
- `is_free`
- `is_active`

`User` mantiene contadores por periodo:

- `ai_text_tokens_used`
- `ai_images_used`
- `ai_videos_used`
- `ai_spend_cents`
- `ai_usage_period_start`
- `ai_plan_id`

Metodos clave:

```php
$user->aiRemaining('image');
$user->aiExceeded('video', 1, $estimatedCostCents);
$user->recordAiUsage('text', $tokens, $costCents);
$user->resetAiPeriodIfNeeded();
```

`config/ai_pricing.php` contiene costos reales aproximados por modelo y `markup`. `AiPricing` calcula costo interno y precio al usuario; `AiQuotaService` valida cuota/presupuesto antes de consumir.

### Acciones AI actuales

- Crear pagina CMS completa.
- Crear post de blog completo.
- Editar contenido existente.
- Generar contenido en Editor.js.
- Generar bloques HTML + Tailwind dentro del bloque `starchoHtml`.
- Generar extracto.
- Generar SEO.
- Auditar contenido.
- Proponer inspiracion.
- Editar paginas Folio.
- Generar SEO de paginas Folio.
- Regenerar desde memoria editorial.
- Generar imagenes y guardarlas como media `ai_image`.
- Generar videos y guardarlos como media `ai_video`.
- Generar imagen destacada de post/pagina desde `/admin/ai/featured-image`.
- Gestionar planes AI y cuotas desde `/admin/ai`.
- Ver costos, tokens perdidos, top users por gasto y assets recientes.

### Trazabilidad AI

- `PostAiGeneration` guarda provider, model, action, locale, prompts, payload, response, tokens, duracion y rating.
- `PostAiMemory` guarda aprendizajes editoriales activos, manuales o derivados de generaciones.
- `PostInsights` muestra stats, historial AI, tokens, ratings, comentarios y memorias.
- `PostComment` agrega comentarios internos con respuestas hasta 3 niveles visuales.
- `AiAssetGeneration` guarda imagenes/videos: type, provider, model, status, external_id, media_id, prompt, params, error, cost, price y duration.

Rutas relacionadas:

- `/admin/ai` para configurar AI, modelos, planes, costos y generar assets.
- `/admin/site` para site manager, SEO, storage y resumen AI.
- `/admin/site/ai` para guardar AI desde Site Manager.
- `/admin/posts` y `/admin/posts/create` para creacion AI de posts.
- `/admin/pages` y `/admin/pages/create` para creacion AI de paginas.
- `/admin/site/page-editor` para editar paginas Folio y SEO con AI.
- `/admin/media` para ver assets generados.

---

## Site Manager

`App\Livewire\Admin\SiteManager` concentra la administracion del sitio dentro de `/admin/site`.

Incluye:

- Branding y metadatos globales.
- Favicon y OG image.
- Idiomas del sitio.
- Redes sociales.
- SEO por paginas Folio detectadas en `resources/views/pages`.
- Home source y pagina CMS home.
- Configuracion AI.
- Estadisticas AI.
- Acceso coordinado con AI Manager para modelos, planes y consumo.
- Configuracion Storage.
- Planes de Storage.

El Site Manager reutiliza controladores existentes mediante requests internos:

- `SiteController`
- `AiSettingsController`
- `StorageSettingsController`

---

## Blog, paginas e insights

`Post` soporta dos tipos:

- `post`: blog.
- `page`: paginas CMS.

El contenido es multilenguaje con Spatie Translatable. Las vistas publicas estan en:

- `resources/views/blog/show.blade.php`
- `resources/views/page/show.blade.php`

Novedades principales:

- Conteo de vistas en `posts.views_count`.
- Galeria por post usando `Media`.
- Editor con subida de imagen.
- AI creator para posts y paginas.
- AI assistant en edicion.
- Post Insights para estadisticas, historial AI, comments y memories.
- Bloque `starchoHtml` para contenido HTML + Tailwind generado o editado.

---

## Editor.js en posts y paginas

El editor principal de contenido vive en `/admin/posts/*` y `/admin/pages/*`. Ambos flujos comparten el formulario:

- `resources/views/admin/posts/_editor-form.blade.php`
- `resources/views/admin/posts/create.blade.php`
- `resources/views/admin/posts/edit.blade.php`
- `resources/views/admin/pages/create.blade.php`
- `resources/views/admin/pages/edit.blade.php`

Rutas principales:

| Ruta | Nombre | Uso |
| --- | --- | --- |
| `/admin/posts/create` | `admin.posts.create` | Crear post con Editor.js o AI |
| `/admin/posts/{post}/edit` | `admin.posts.edit` | Editar post, galeria y AI assistant |
| `/admin/pages/create` | `admin.pages.create` | Crear pagina CMS con Editor.js o AI |
| `/admin/pages/{post}/edit` | `admin.pages.edit` | Editar pagina CMS |
| `/admin/posts/upload-image` | `admin.posts.upload-image` | Subida de imagen desde Editor.js |
| `/admin/posts/{post}/gallery` | `admin.posts.gallery.upload` | Agregar imagenes a la galeria del contenido |
| `/admin/posts/{post}/gallery/{media}` | `admin.posts.gallery.destroy` | Quitar imagen de la galeria |

### Herramientas cargadas

El formulario carga Editor.js desde CDN y registra herramientas base:

- Header
- List
- Paragraph
- Quote
- Code
- Delimiter
- Image
- Embed
- Table
- Warning
- InlineCode
- Marker
- `starchoHtml`

El bloque `starchoHtml` esta implementado en `public/js/starcho-html-editor.js`. Guarda dos campos:

- `html`: markup renderizable.
- `css`: estilos propios del bloque.

El bloque ofrece:

- Textareas para HTML y CSS.
- Preview renderizado dentro del editor.
- Boton para ocultar/mostrar preview.
- Modo `Editar vista` con `contentEditable`, que permite ajustar texto directamente en la vista y sincronizarlo de vuelta al HTML.
- Soporte esperado para modo claro/oscuro mediante clases Tailwind `dark:*`, CSS compatible con `.dark` y `prefers-color-scheme`.

### AI dentro del editor

La AI de contenido se coordina con:

- `App\Livewire\Admin\PostAiCreator`
- `App\Livewire\Admin\PageAiCreator`
- `App\Livewire\Admin\PageAiAssistant`
- `App\Services\PageAiContentService`
- `App\Models\PostAiGeneration`
- `App\Models\PostAiMemory`

Formatos disponibles:

- `editorjs`: genera bloques editables nativos de Editor.js.
- `html`: genera una pieza visual HTML + Tailwind dentro de `starchoHtml`.

Cuando el formato es HTML, los prompts pasan parametros obligatorios para:

- Usar HTML semantico y responsive.
- Evitar scripts, iframes y assets externos.
- Incluir estados claro/oscuro con `dark:*`.
- Evitar `bg-white`, `text-zinc-900` u otros colores rigidos sin equivalente `dark:*`.
- Mantener contraste AA, espaciados consistentes y componentes legibles en mobile/desktop.

El assistant de edicion puede actuar sobre:

- Contenido completo.
- Extracto.
- SEO.
- Inspiracion.
- Auditoria.
- Regeneracion con memory.

`memory_regenerate` usa memorias editoriales seleccionadas de `PostAiMemory` y puede devolver Editor.js estructurado o `starchoHtml`, segun el formato elegido por el usuario.

### Controles Starcho del editor

Los controles reutilizables deben vivir como componentes `x-starcho-editorjs-*` antes de duplicar markup:

| Componente | Uso |
| --- | --- |
| `x-starcho-editorjs-ai-actions` | Botones AI del editor, incluido regenerar con memory |
| `x-starcho-editorjs-panel-controls` | Controles de sidebar/panel del editor |
| `x-starcho-editorjs-ai-spend` | Popup/resumen de gasto AI en posts y paginas |

Reglas:

- Si un control se repite en posts y paginas, convertirlo en `resources/views/components/starcho-editorjs-*.blade.php`.
- Mantener el estilo en `/admin` sobre Flux UI, Tailwind y componentes Starcho.
- Usar `Starcho.confirm` para confirmaciones destructivas.
- Persistir preferencias de UI con `localStorage` cuando sean por usuario/navegador, como panel abierto/cerrado o posicion del sidebar.

### Imagenes, galeria y storage

La subida de imagenes del editor y la galeria pasan por `PostController` y `StorageService`:

- `PostController::uploadEditorImage()` guarda imagenes insertadas dentro de Editor.js.
- `PostController::uploadGalleryImage()` guarda imagenes de galeria con contexto `gallery`.
- `PostController::destroyGalleryImage()` quita una imagen de la galeria del post/pagina.
- `Post::gallery()` devuelve los `Media` asociados al contenido.

La galeria se muestra al final del post o pagina publica en:

- `resources/views/blog/show.blade.php`
- `resources/views/page/show.blade.php`

Storage se resuelve desde la configuracion activa del sitio:

- Local: usa `StorageSetting::localPublicUrl` para construir URL publica y evita depender de `localhost`.
- R2: usa `r2_public_url` si existe; si no, URL temporal del disco.
- S3/Spaces: usa la URL publica del disco configurado.
- Variantes: pueden entregarse con `/media/files/{media}?variant=...`.

### Render publico

Las vistas publicas leen el JSON de Editor.js y renderizan los bloques soportados. Para `starchoHtml`, el render inyecta el HTML y CSS guardados por el bloque, por eso las reglas de seguridad y estilo se deben aplicar desde la generacion y desde la edicion:

- No guardar scripts.
- No depender de assets externos.
- Mantener clases Tailwind compatibles con el build.
- Revisar que el HTML generado funcione en light y dark mode.

### Checklist para tocar Editor.js

- Revisar primero `resources/views/admin/posts/_editor-form.blade.php`.
- Reutilizar o crear un componente `x-starcho-editorjs-*` si el cambio aplica a posts y paginas.
- Si hay AI, actualizar prompts en `PostAiCreator`, `PageAiCreator` y/o `PageAiContentService`.
- Si hay archivos, usar `StorageService` y respetar local/R2/S3.
- Si hay galeria publica, validar `blog/show.blade.php` y `page/show.blade.php`.
- Ejecutar `php artisan view:cache` y `npm run build`.

---

## Modulos y menu dinamico

Los modulos se administran desde `/admin/modules`.

Modelo:

- `app/Models/StarchoModule.php`

Menu:

- `app/Models/StarchoMenuItem.php`
- `app/Models/StarchoMenuSection.php`
- `/admin/menu`

Ciclo:

```php
$module->install();
$module->activate();
$module->deactivate();
$module->uninstall();

StarchoModule::isActive('contacts');
StarchoMenuItem::getCachedMenu();
StarchoMenuItem::clearMenuCache();
```

Regla: desinstalar un modulo no debe borrar datos de negocio; solo retira entradas del menu y cambia estado.

---

## Assets

Cada area carga sus propios assets.

| Archivo | Uso |
| --- | --- |
| `resources/css/app.css` | Tailwind + Flux base |
| `resources/css/starcho-admin.css` | Ajustes exclusivos de `/admin` |
| `resources/css/starcho-app.css` | Layout privado `/app` |
| `resources/css/starcho-auth.css` | Login/register/reset |
| `resources/css/starcho-home.css` | Home publica |
| `resources/css/starcho-components.css` | Componentes compartidos |
| `resources/js/starcho.js` | Utilidades globales |
| `resources/js/admin.js` | Bundle admin |
| `resources/js/app.js` | Bundle app |
| `public/js/starcho-html-editor.js` | Editor HTML del bloque `starchoHtml` |

Reglas:

- No mezclar CSS de `/app` con `/admin`.
- No crear JS ad-hoc si Livewire/Alpine o `Starcho.*` resuelven el flujo.
- No instanciar graficas manualmente si `x-starcho-chart` aplica.

---

## Skill Starcho: construir apps con Laravel + Starcho

Usa este skill como contrato operativo cuando se cree una nueva app, modulo o pantalla dentro de Starcho.

### Identidad

Eres un builder senior de Starcho. Construyes con Laravel 13, Livewire 4, Flux UI en `/admin`, PowerGrid para tablas y componentes del Star Kit. Tu prioridad es entregar una app mantenible, modular, traducible y consistente con el sistema existente.

### Fuentes de verdad

1. `README.md`
2. `MODULES_AND_MENU.md`
3. Rutas en `routes/admin.php`, `routes/app.php`, `routes/web.php`
4. Componentes en `resources/views/components`
5. Layouts en `resources/views/layouts`

### Reglas de construccion

1. Dar siempre rutas concretas: path HTTP, route name y archivo donde se implementa.
2. En `/admin`, usar Flux UI como base visual.
3. Reutilizar componentes `x-starcho-*` antes de escribir markup nuevo.
4. Si hay tabla, usar PowerGrid.
5. Si hay archivos, usar `StorageService`.
6. Si hay AI de texto, usar `AiSetting`, `PageAiContentService` y registrar trazabilidad.
7. Si hay AI de imagen/video, usar `AiImageService`, `AiVideoService` o `AiReplicateService`; nunca llamar providers directo desde Blade/Livewire.
8. Si hay consumo AI, validar `AiQuotaService` y registrar costo/uso en `AiAssetGeneration` o `PostAiGeneration`.
9. Si hay contenido traducible, usar Spatie Translatable y lang files.
10. Si hay `user_id`, aplicar ownership real en modelo o query.
11. Si hay CRUD Livewire, usar `DispatchesStarchoNotify`.
12. Si algo se repite dos veces, convertirlo en componente Starcho.

### Estructura recomendada para un modulo app

```text
app/Models/<Model>.php
app/Livewire/App/<Module>Table.php
resources/views/<module>/index.blade.php
resources/views/<module>/pg-header.blade.php
resources/views/livewire/app/<module>-modal.blade.php
routes/app.php
lang/es/<module>.php
lang/en/<module>.php
lang/pt_BR/<module>.php
database/migrations/xxxx_xx_xx_xxxxxx_create_<module>_table.php
```

### Estructura recomendada para una pantalla admin

```text
app/Http/Controllers/Admin/<Module>Controller.php
app/Livewire/Admin/<Module>Table.php
resources/views/admin/<module>/index.blade.php
resources/views/admin/<module>/pg-header.blade.php
resources/views/livewire/admin/<module>-modal.blade.php
routes/admin.php
lang/es/admin_ui.php
lang/en/admin_ui.php
lang/pt_BR/admin_ui.php
```

### Checklist antes de terminar

- Ruta registrada y nombrada.
- Middleware correcto.
- UI basada en Flux en admin.
- Componentes Starcho reutilizados.
- Textos traducibles.
- Storage via `StorageService` si aplica.
- AI texto via `PageAiContentService` si aplica.
- AI imagen/video via servicios `App\Services\Ai\*` si aplica.
- Cuotas y costos AI validados si aplica.
- Ownership si aplica.
- Notificaciones con `DispatchesStarchoNotify`.
- Cache/menu invalidado si toca modulos o menu.
- `npm run build`, `php artisan route:list` y `php artisan test` cuando el entorno tenga PHP disponible.

---

## Comandos utiles

```bash
npm run dev
npm run build

php artisan starcho:install
php artisan migrate:fresh --seed --seeder=StarchoInstallAppSeeder
php artisan route:list --path=admin
php artisan route:list --path=app
php artisan optimize:clear
php artisan optimize
php artisan test
./vendor/bin/pint
```

---

## Agente del proyecto

El repo incluye un agente especializado:

- `.github/agents/galax-starcho.agent.md`

Debe mantenerse alineado con este README. Si cambian rutas, componentes, storage, AI o convenciones del Star Kit, actualizar README primero y despues el agente.

---

## Licencia

MIT
