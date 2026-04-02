/**
 * admin.js — Entry point del área /admin
 * ========================================
 * Cargado exclusivamente en layouts/admin/sidebar.blade.php.
 *
 * Responsabilidades:
 *   1. Importar la librería compartida starcho.js (incluye window.starchoDelete
 *      que usan los botones de PowerGrid en el panel admin).
 *   2. Inicializar PowerGrid para todas las tablas del /admin.
 *   3. Registrar el componente Alpine 'adminLayout' para el panel.
 *
 * NO incluye código específico de /app.
 */

// ── Librería compartida ────────────────────────────────────────────────────
import './starcho.js';

// ── PowerGrid — tablas reactivas (RolesTable, PermissionsTable, etc.) ─────
import '../../vendor/power-components/livewire-powergrid/dist/powergrid.js';

// ─────────────────────────────────────────────────────────────────────────────
// COMPONENTE ALPINE — adminLayout()
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Componente Alpine para el layout del /admin.
 * Se puede usar como x-data="adminLayout()" si se necesita estado global en admin.
 *
 * Estado:
 *   - (reservado para futuras expansiones del panel admin)
 *
 * Actualmente el admin usa Flux sidebar que maneja su propio estado.
 * Este componente existe para mantener consistencia y facilitar extensiones.
 */
/**
 * adminLayout()
 * ─────────────────────────────────────────────────────────────────────────────
 * Componente Alpine del layout /admin.
 *
 * Estado:
 *   isDark    — modo oscuro (sincronizado con <html class="dark">)
 *   collapsed — sidebar colapsado (persistido en localStorage)
 *   mobOpen   — sidebar abierto en móvil
 */
window.adminLayout = function () {
    return {
        isDark:    false,
        collapsed: false,
        mobOpen:   false,
        showLogout: false,

        init() {
            // Usar el sistema unificado de tema de Starcho
            this.isDark = window.Starcho.dark.isDark();

            // Aplicar el tema al cargar
            window.Starcho.dark.apply();

            // Inicializar sincronización con Flux
            window.Starcho.dark.init();

            // Cargar estado del sidebar desde localStorage
            try {
                this.collapsed = localStorage.getItem('sa_collapsed') === 'true';
            } catch (e) {}

            // Sincronizar isDark → Starcho.dark
            this.$watch('isDark', v => {
                window.Starcho.dark.set(v ? 'dark' : 'light');
            });

            // Persistir collapsed
            this.$watch('collapsed', v => {
                try { localStorage.setItem('sa_collapsed', String(v)); } catch (e) {}
            });
        },

        /** Muestra un toast. Delega en window.Starcho.notify. */
        notify(type, message) {
            window.Starcho.notify(type, message);
        },
    };
};
