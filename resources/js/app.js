/**
 * app.js — Entry point del área /app
 * ====================================
 * Cargado exclusivamente en layouts/app/sidebar.blade.php.
 *
 * Responsabilidades:
 *   1. Importar la librería compartida starcho.js (Starcho.confirm, Starcho.notify,
 *      starchoApp Alpine component, window.starchoDelete, etc.)
 *   2. Inicializar PowerGrid para las tablas del área /app.
 *
 * NO incluye código específico de /admin.
 */

// ── Librería compartida ────────────────────────────────────────────────────
import './starcho.js';

// ── PowerGrid — tablas reactivas (ContactsTable, UserTasksTable) ───────────
import '../../vendor/power-components/livewire-powergrid/dist/powergrid.js';
