# PRD: Starcho

Última actualización: 2026-05-28

## 1. Resumen

Starcho es un starter kit modular para construir aplicaciones web tipo SaaS sobre Laravel. El producto entrega una base lista para producción con autenticación, panel administrativo, área privada de usuario, gestión de módulos, permisos, blog multilenguaje, almacenamiento multimedia, suscripciones, geolocalización, asistencia de IA para contenido y herramientas operativas.

El objetivo principal es reducir el tiempo necesario para iniciar nuevos proyectos Laravel con una arquitectura extensible, mantenible y preparada para crecer mediante módulos instalables.

## 2. Problema

Crear una aplicación SaaS desde cero suele exigir repetir una base común: autenticación, roles, permisos, CRUDs administrativos, menús, layouts, traducciones, configuración del sitio, almacenamiento, contenido, dashboards y scripts de instalación. Esta repetición consume tiempo, introduce inconsistencias y retrasa la entrega de valor específico del negocio.

Starcho resuelve este problema ofreciendo una plataforma inicial modular que permite arrancar rápido, mantener una separación clara entre administración y experiencia del usuario, y agregar nuevas funcionalidades sin rehacer la estructura principal.

## 3. Objetivos

- Proveer una base Laravel moderna para nuevos productos SaaS o aplicaciones internas.
- Permitir instalar, activar, desactivar y desinstalar módulos desde el panel administrativo.
- Ofrecer un panel administrativo completo para usuarios, roles, permisos, contenido, almacenamiento, caché y configuración del sitio.
- Incluir un área privada de usuario con dashboard y módulos iniciales como tareas, contactos y notas.
- Soportar internacionalización desde el inicio en español, inglés y portugués de Brasil.
- Integrar capacidades de IA para acelerar la creación, edición, análisis y mejora de contenido.
- Facilitar despliegue y desarrollo local con comandos de instalación y build documentados.
- Mantener una arquitectura extensible con convenciones claras para crear nuevos módulos.

## 4. No Objetivos

- No pretende ser una aplicación final de un solo dominio de negocio.
- No reemplaza un CMS completo especializado, aunque incluye blog, páginas y configuración de contenido.
- No define reglas comerciales específicas para industrias como finanzas, salud, educación o comercio electrónico.
- No busca cubrir todos los modelos de monetización posibles en la primera versión.

## 5. Usuarios Objetivo

### Desarrollador Laravel

Necesita una base confiable para iniciar proyectos rápidamente, con convenciones claras, módulos reutilizables y stack moderno.

### Administrador del Sistema

Gestiona usuarios, roles, permisos, módulos, menús, contenido, configuración del sitio, almacenamiento, caché y seguridad operativa.

### Usuario Final Autenticado

Accede al área `/app` para usar funcionalidades privadas como dashboard, tareas, contactos y notas.

### Dueño del Producto

Quiere lanzar nuevas aplicaciones o verticales rápidamente sin invertir semanas en construir la infraestructura común.

## 6. Propuesta de Valor

Starcho permite iniciar un proyecto Laravel con una base administrativa y modular ya resuelta. Combina una experiencia de administración robusta con un área de usuario extensible, reduciendo trabajo repetitivo y dando un camino claro para agregar nuevos módulos.

## 7. Alcance Funcional

### 7.1 Autenticación y Seguridad

- Registro, inicio de sesión y verificación de usuarios.
- Autenticación con Laravel Fortify.
- Soporte para autenticación de dos factores.
- Control de acceso por roles y permisos con Spatie Laravel Permission.
- Middleware para proteger `/admin` y `/app`.
- Sistema de baneo temporal o permanente de usuarios.

### 7.2 Panel Administrativo

El área `/admin` debe permitir:

- Visualizar dashboard administrativo.
- Gestionar usuarios.
- Gestionar roles y permisos.
- Administrar tareas, contactos y notas.
- Instalar, desinstalar, activar y desactivar módulos.
- Administrar ítems de menú.
- Limpiar caché de aplicación, rutas, configuración, vistas, permisos y menú.
- Gestionar configuración del sitio.
- Gestionar blog, páginas, categorías y etiquetas.
- Administrar galería multimedia y archivos.
- Administrar álbumes multimedia, comentarios, valoraciones, favoritos y variantes de imagen.
- Configurar almacenamiento y planes.
- Configurar proveedores y modelos de IA.
- Consultar geolocalizaciones de usuarios.
- Gestionar baneos de usuarios.

### 7.3 Área de Usuario

El área `/app` debe permitir:

- Acceder a un dashboard privado.
- Gestionar tareas propias.
- Gestionar contactos propios.
- Gestionar notas propias.
- Exportar datos de módulos soportados.
- Respetar ownership por usuario en modelos con `user_id`.

### 7.4 Sistema de Módulos

El sistema debe permitir:

- Registrar módulos con clave única, nombre, descripción, icono, estado de instalación, estado activo y configuración JSON.
- Definir ítems de menú dentro de la configuración del módulo.
- Crear automáticamente ítems de menú al instalar un módulo.
- Eliminar ítems de menú al desinstalar un módulo.
- Activar o desactivar módulos sin eliminar datos.
- Mantener caché de estado de módulos y menú.
- Seguir convenciones documentadas para módulos de `/app` y `/admin`.

### 7.5 Sistema de Menú

El menú debe permitir:

- Mostrar opciones por panel (`app` o `admin`).
- Agrupar ítems por sección.
- Soportar jerarquía mediante `parent_id`.
- Usar rutas nombradas o URLs externas.
- Ordenar ítems mediante `sort_order`.
- Activar o desactivar ítems.
- Sincronizar ítems generados por módulos.

### 7.6 Blog, Páginas y Contenido

El sistema de contenido debe permitir:

- Crear y editar posts multilenguaje.
- Crear y editar páginas estáticas.
- Gestionar categorías y etiquetas traducibles.
- Generar slugs traducibles.
- Configurar campos SEO y sitemap.
- Publicar blog público por idioma.
- Registrar vistas de posts.
- Consultar insights de posts, incluyendo métricas, generaciones de IA, comentarios y memorias.
- Generar sitemap XML.
- Administrar enlaces rotos.

### 7.7 Internacionalización

El producto debe soportar:

- Español como idioma por defecto.
- Inglés.
- Portugués de Brasil.
- Cambio de idioma mediante ruta pública.
- Persistencia del locale en el usuario.
- Traducciones de UI en archivos `lang`.
- Modelos traducibles donde aplique.

### 7.8 Almacenamiento, Media y Suscripciones

El sistema debe permitir:

- Definir planes de almacenamiento.
- Asignar plan de almacenamiento a usuarios.
- Calcular uso, límite, porcentaje y espacio restante.
- Subir y gestionar archivos multimedia.
- Registrar metadatos de archivos.
- Organizar media en álbumes.
- Adjuntar o separar archivos de álbumes.
- Ejecutar acciones masivas sobre media.
- Generar variantes de imagen para archivos individuales o en lote.
- Descargar archivos desde el panel.
- Registrar comentarios, valoraciones y favoritos sobre media cuando aplique.
- Configurar URL local, carpeta y parámetros de procesamiento de media.
- Crear suscripciones activas al crear usuarios.
- Mantener estado y periodo de suscripciones.

### 7.9 Inteligencia Artificial para Contenido

El producto debe permitir:

- Configurar proveedores de IA desde el panel administrativo.
- Soportar OpenAI, DeepSeek, Anthropic y OpenRouter.
- Guardar claves de API de forma cifrada.
- Definir modelo por defecto y modelos activos por proveedor.
- Generar, reescribir o mejorar contenido de posts y páginas desde el admin.
- Registrar cada generación de IA con prompt, system prompt, payloads, respuesta, tokens y duración.
- Registrar métricas de calidad mediante rating y notas.
- Crear memorias de IA asociadas a posts para reutilizar contexto editorial.
- Consultar estadísticas por proveedor y modelo.
- Mostrar historial reciente de generaciones.

### 7.10 Gestor Unificado del Sitio

El admin debe contar con una experiencia centralizada para gestionar:

- Configuración general del sitio.
- SEO, metadatos, favicon e imagen Open Graph.
- Idiomas disponibles.
- Redes sociales.
- Configuración de IA.
- Configuración de storage.
- Planes de almacenamiento.
- SEO de páginas Folio y páginas CMS.
- Métricas relacionadas con contenido e IA.

### 7.11 Importación y Exportación

El producto debe soportar exportación e importación de datos para entidades administrativas y de usuario donde aplique:

- Roles.
- Permisos.
- Usuarios.
- Tareas.
- Contactos.
- Notas.
- Menú.
- Módulos.

### 7.12 Geolocalización

El sistema debe:

- Registrar accesos autenticados con IP, país, ciudad y coordenadas cuando estén disponibles.
- Cachear resolución de IP para evitar consultas repetidas.
- Mostrar registros desde el panel administrativo.

## 8. Requisitos No Funcionales

### Rendimiento

- El menú lateral y estados de módulos deben usar caché.
- Las tablas administrativas deben ser reactivas y paginadas.
- El build frontend debe optimizarse mediante Vite.
- Las consultas de insights, generaciones y media deben usar agregaciones y cargas relacionadas controladas.

### Seguridad

- Las rutas administrativas deben exigir autenticación, verificación, rol autorizado y permiso `view-admin`.
- Las rutas de usuario deben exigir autenticación, verificación y ausencia de ban activo.
- Los modelos con datos de usuario deben aplicar ownership.
- Las acciones destructivas deben usar confirmación UI.
- Las claves de proveedores de IA deben almacenarse cifradas.
- Las operaciones de media deben validar permisos, tamaños, rutas y ownership cuando aplique.

### Mantenibilidad

- Los módulos deben seguir la estructura documentada.
- Los textos de interfaz deben vivir en archivos de traducción.
- Los estilos del admin deben usar prefijo `sa-` para evitar colisiones.
- El área `/app` y `/admin` deben mantener assets separados.

### Compatibilidad

- PHP 8.3 o superior.
- Laravel 13.
- Livewire 4.
- Tailwind CSS 4.
- Vite 6.
- Soporte para MySQL y PostgreSQL según instalación guiada.

### Usabilidad

- El administrador debe poder operar módulos, menú, caché, usuarios y contenido sin tocar código.
- La UI debe mostrar notificaciones claras para operaciones CRUD.
- La experiencia debe estar localizada en los idiomas soportados.

## 9. Stack Tecnológico

- Backend: Laravel 13, PHP 8.3+.
- Reactividad frontend: Livewire 4 y Alpine.js.
- UI admin: Flux UI v2.
- Tablas: PowerGrid v6.
- Estilos: Tailwind CSS v4.
- Build: Vite 6.
- Autenticación: Laravel Fortify.
- Permisos: Spatie Laravel Permission.
- Traducciones de modelos: Spatie Laravel Translatable.
- Importación/exportación: Maatwebsite Excel y OpenSpout.
- Notificaciones JS: Notiflix.
- Gráficas: ApexCharts.
- IA configurable: OpenAI, DeepSeek, Anthropic y OpenRouter.

## 10. Experiencia de Instalación

El producto debe proveer un comando de instalación:

```bash
php artisan starcho:install
```

El instalador debe:

- Crear `.env` desde `.env.example` si no existe.
- Solicitar motor y credenciales de base de datos.
- Instalar dependencias de Composer y npm.
- Ejecutar migraciones y seeders iniciales.
- Crear usuario administrador.
- Registrar roles, permisos, módulos y menú inicial.
- Crear enlace de storage cuando aplique.
- Generar assets de producción.

Credenciales iniciales de desarrollo:

- Email: `admin@starcho.com`.
- Password: `password`.
- Rol: `admin`.

## 11. Entidades Principales

- `User`.
- `Role`.
- `Permission`.
- `StarchoModule`.
- `StarchoMenuItem`.
- `StarchoMenuSection`.
- `Task`.
- `Contact`.
- `Note`.
- `Post`.
- `PostCategory`.
- `PostTag`.
- `PostAiGeneration`.
- `PostAiMemory`.
- `PostComment`.
- `Media`.
- `MediaAlbum`.
- `StoragePlan`.
- `StorageSetting`.
- `AiSetting`.
- `Subscription`.
- `SiteSetting`.
- `SiteLanguage`.
- `UserBan`.
- `UserGeoLocation`.
- `GeoIPCache`.
- `BrokenLink`.
- `ContentSetting`.

## 12. Rutas Principales

- `/admin`: panel administrativo.
- `/app`: área privada de usuario.
- `/admin/site`: gestor de sitio, SEO, IA y storage.
- `/admin/site/ai`: actualización de configuración de IA.
- `/admin/media`: galería multimedia.
- `/admin/media/albums`: álbumes multimedia.
- `/admin/posts`: gestión de blog y contenido.
- `/admin/pages`: gestión de páginas CMS.
- `/language/{locale}`: cambio de idioma.
- `/{locale}/blog`: blog público por idioma.
- `/{locale}/blog/{slug}`: detalle de post.
- `/{locale}/{slug}`: página pública.
- `/sitemap.xml`: sitemap.

## 13. Métricas de Éxito

- Tiempo para instalar y levantar el proyecto localmente menor a 15 minutos.
- Tiempo para crear un módulo básico menor a 1 día de trabajo.
- 100% de módulos core accesibles desde menú generado o administrable.
- Operaciones CRUD principales disponibles desde UI sin intervención manual en base de datos.
- Cobertura de traducciones para español, inglés y portugués de Brasil en las pantallas core.
- Generaciones de IA registradas con trazabilidad de proveedor, modelo, tokens, duración y rating.
- Media organizada en álbumes y con variantes generables desde el panel.
- Build de producción y pruebas base ejecutándose sin errores antes de release.

## 14. Criterios de Aceptación MVP

- El comando de instalación configura una instancia funcional.
- Un administrador puede iniciar sesión y acceder a `/admin`.
- Un usuario autenticado puede acceder a `/app`.
- El administrador puede gestionar usuarios, roles y permisos.
- El administrador puede instalar y desinstalar módulos.
- La instalación de un módulo crea sus ítems de menú configurados.
- La desinstalación de un módulo elimina sus ítems de menú asociados.
- Tareas, contactos y notas funcionan en el área de usuario.
- El blog público sirve contenido multilenguaje.
- El sitemap está disponible.
- El sistema permite subir y gestionar media.
- El sistema permite organizar media en álbumes y ejecutar acciones masivas.
- El administrador puede configurar proveedores y modelos de IA.
- El administrador puede generar contenido asistido por IA y revisar historial de generaciones.
- El administrador puede consultar insights, comentarios y memorias de posts.
- El cambio de idioma funciona para los locales soportados.

## 15. Riesgos y Consideraciones

- Laravel 13, Livewire 4 y algunas dependencias pueden tener cambios frecuentes; se requiere seguimiento de compatibilidad.
- La generación automática de menú depende de rutas nombradas válidas.
- Los módulos nuevos pueden romper convenciones si no siguen el contrato documentado.
- La geolocalización depende de servicios o datos externos y puede no estar siempre disponible.
- El almacenamiento debe validar límites y permisos con cuidado para evitar abuso.
- El soporte multilenguaje exige disciplina en traducciones para evitar textos hardcodeados.
- Las integraciones de IA dependen de proveedores externos, costos variables, cuotas y disponibilidad.
- La generación de variantes de media puede consumir CPU, memoria y almacenamiento de forma significativa.

## 16. Roadmap Sugerido

### Fase 1: Base Estable

- Consolidar instalación guiada.
- Validar migraciones y seeders iniciales.
- Asegurar rutas principales.
- Verificar CRUDs core.
- Documentar creación de módulos.

### Fase 2: Experiencia Modular

- Mejorar configuración visual de módulos.
- Agregar validación de rutas en configuración de menú.
- Exponer estado y dependencias de módulos.
- Incorporar plantillas o generadores para módulos nuevos.

### Fase 3: Operación SaaS

- Expandir gestión de suscripciones.
- Agregar límites por plan más allá del almacenamiento.
- Mejorar dashboard administrativo con métricas de uso.
- Agregar auditoría de acciones administrativas.
- Consolidar métricas de IA por costo, proveedor, modelo y calidad.

### Fase 4: Contenido y Crecimiento

- Mejorar editor de páginas y posts.
- Agregar programación de publicaciones.
- Mejorar SEO técnico y reportes de sitemap.
- Fortalecer gestión de enlaces rotos.
- Convertir insights de posts en recomendaciones editoriales accionables.
- Mejorar workflows de media con procesamiento asíncrono de variantes.

## 17. Preguntas Abiertas

- ¿Starcho se distribuirá como starter kit interno, producto open source o base comercial?
- ¿Los módulos tendrán dependencias entre sí?
- ¿Se requiere marketplace o repositorio remoto de módulos?
- ¿Las suscripciones se integrarán con pasarelas de pago?
- ¿Qué nivel de auditoría legal o trazabilidad se necesita para acciones administrativas?
- ¿Se espera soporte multi-tenant en futuras versiones?
- ¿Se medirá el costo real de IA por proveedor/modelo/usuario?
- ¿Las memorias de IA serán globales, por post, por idioma o por workspace?
- ¿Las variantes de media se generarán sincrónicamente o mediante jobs en cola?
