<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indices para las dos formas de consulta que dominan el trafico publico.
 *
 * La tabla `posts` solo tenia el unique de `slug` y los indices implicitos de las
 * claves foraneas, asi que toda consulta del blog terminaba en un full table scan
 * y en un filesort para ordenar.
 *
 * 1. posts_public_listing_index (type, status, published_at)
 *    Cubre BlogController::index, los posts relacionados, el sitemap de posts y
 *    los contadores del dashboard:
 *
 *        where type = 'post' and status = 'published'
 *          and (published_at is null or published_at <= now())
 *        order by published_at desc
 *
 *    El orden de las columnas importa: primero las de igualdad (type, status) y
 *    al final la del rango + ORDER BY (published_at). Asi MySQL resuelve el filtro
 *    y la ordenacion con el mismo indice, sin filesort.
 *
 * 2. posts_menu_order_index (type, status, menu_order)
 *    Cubre las cuatro consultas de paginas para navegacion y sitemap:
 *
 *        where type = 'page' and status = 'published' order by menu_order
 *
 *    Comparte prefijo con el anterior, pero se anade como indice propio porque
 *    ordena por otra columna. En un CMS las lecturas superan a las escrituras por
 *    varios ordenes de magnitud, asi que el coste extra en INSERT/UPDATE es
 *    despreciable frente al ahorro en cada visita.
 *
 * No se indexa `deleted_at`: los soft deletes en un CMS son una fraccion minima
 * de las filas y el motor descarta esas pocas al leer la fila.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->index(['type', 'status', 'published_at'], 'posts_public_listing_index');
            $table->index(['type', 'status', 'menu_order'], 'posts_menu_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex('posts_public_listing_index');
            $table->dropIndex('posts_menu_order_index');
        });
    }
};
