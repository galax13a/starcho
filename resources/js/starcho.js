/**
 * starcho.js — Librería compartida de Starcho
 * ============================================
 * Funciones y componentes Alpine reutilizados entre /app y /admin.
 *
 * Uso en cualquier blade:
 *   window.Starcho.confirm({ ... })
 *   window.Starcho.notify('success', 'Guardado correctamente')
 *   Starcho.dark.toggle()
 *
 * Los dos entry points (app.js y admin.js) importan este módulo.
 */

import Notiflix from 'notiflix';

function tr(key, fallback) {
    return window.StarchoLang?.[key] ?? fallback;
}

// ─────────────────────────────────────────────────────────────────────────────
// SECCIÓN 1 · CONFIRMACIONES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Muestra un diálogo de confirmación estilizado (usa Notiflix internamente).
 *
 * @param {Object}   opts
 * @param {string}   opts.title        Título del diálogo.
 * @param {string}   opts.message      Cuerpo del mensaje.
 * @param {string}   [opts.okText]     Texto del botón "confirmar"  (default: 'Sí, eliminar').
 * @param {string}   [opts.cancelText] Texto del botón "cancelar"   (default: 'Cancelar').
 * @param {Function} opts.onConfirm    Callback al confirmar.
 * @param {Function} [opts.onCancel]   Callback al cancelar.
 */
function confirm(opts) {
    Notiflix.Confirm.show(
        opts.title   ?? tr('confirm_title', 'Confirm action'),
        opts.message ?? tr('confirm_message', 'Are you sure? This action cannot be undone.'),
        opts.okText     ?? tr('confirm_ok', 'Yes, continue'),
        opts.cancelText ?? tr('confirm_cancel', 'Cancel'),
        opts.onConfirm  ?? function () {},
        opts.onCancel   ?? function () {},
        {
            backOverlayColor : 'rgba(0,0,10,0.55)',
            cssAnimationStyle: 'zoom',
            cssAnimation     : true,
            backgroundColor  : '#1a1a2e',
            titleColor       : '#f0f0f5',
            messageColor     : '#a0a0b4',
            textColor        : '#f0f0f5',
            okButtonBackground  : '#fe2c55',
            cancelButtonBackground: '#333344',
            onReady() {
                const btn = document.querySelector('#NXConfirmButtonOk');
                if (btn) { btn.tabIndex = 0; btn.focus(); }
            },
        }
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// SECCIÓN 2 · NOTIFICACIONES / TOASTS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Despacha un evento 'notify' que los layouts de Starcho escuchan para mostrar
 * un toast. Funciona tanto en /app (Alpine custom layout) como en /admin
 * (siempre que el layout incluya el listener @notify.window).
 *
 * @param {'success'|'warning'|'error'} type    Tipo de alerta.
 * @param {string}                      message  Texto a mostrar.
 */
function notify(type, message) {
    const fallbackByType = {
        success: tr('toast_success', 'Success'),
        warning: tr('toast_warning', 'Warning'),
        error  : tr('toast_error', 'Error'),
    };

    const finalMessage = message || fallbackByType[type] || tr('toast_default', 'Operation completed.');

    window.dispatchEvent(
        new CustomEvent('notify', { detail: { type, message: finalMessage } })
    );
}

/**
 * Alias de notify. Compatibilidad semántica.
 * @param {'success'|'warning'|'error'} type
 * @param {string} message
 */
const alert = notify;

// ─────────────────────────────────────────────────────────────────────────────
// SECCIÓN 3 · MODO OSCURO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Gestor de tema oscuro/claro.
 * Guarda la preferencia en localStorage como 'starcho_theme'.
 *
 * @namespace Starcho.dark
 */
const dark = {
    /** Clave de localStorage. */
    _key: 'starcho_theme',

    /** Clave de apariencia de Flux en localStorage. */
    _fluxKey: 'flux.appearance',

    /**
     * Obtiene la preferencia de apariencia desde Flux.
     * @returns {'dark'|'light'|'system'|null}
     */
    getFluxAppearance() {
        if (window.Flux && window.Flux.appearance) {
            return window.Flux.appearance;
        }

        // Compatibilidad con implementaciones antiguas
        if (window.$flux && window.$flux.appearance) {
            return window.$flux.appearance;
        }

        const stored = localStorage.getItem(this._fluxKey);
        return stored === 'dark' || stored === 'light' || stored === 'system' ? stored : null;
    },

    /**
     * ¿El tema actual es oscuro?
     * @returns {boolean}
     */
    isDark() {
        // 1) Preferencia Flux (fuente principal del sistema de apariencia)
        const fluxAppearance = this.getFluxAppearance();
        if (fluxAppearance) {
            if (fluxAppearance === 'dark') return true;
            if (fluxAppearance === 'light') return false;
            if (fluxAppearance === 'system') {
                return window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
        }

        // 2) Fallback a preferencia propia de Starcho
        const stored = localStorage.getItem(this._key);
        if (stored === 'dark') return true;
        if (stored === 'light') return false;
        if (stored === 'system') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        // 3) Último fallback: estado actual del DOM (evita desincronizar admin)
        return document.documentElement.classList.contains('dark');
    },

    /**
     * Aplica el tema al elemento raíz (añade/quita clase 'dark').
     * Incluye transición suave.
     */
    apply() {
        const html = document.documentElement;
        const isCurrentlyDark = html.classList.contains('dark');
        const shouldBeDark = this.isDark();

        if (isCurrentlyDark !== shouldBeDark) {
            // Agregar transición suave
            html.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            setTimeout(() => {
                html.classList.toggle('dark', shouldBeDark);
                // Remover transición después de completar
                setTimeout(() => {
                    html.style.transition = '';
                }, 300);
            }, 10);
        } else {
            html.classList.toggle('dark', shouldBeDark);
        }
    },

    /**
     * Alterna entre oscuro y claro, persiste y aplica.
     */
    toggle() {
        localStorage.setItem(this._key, this.isDark() ? 'light' : 'dark');
        this.apply();
    },

    /**
     * Fuerza un tema específico.
     * @param {'dark'|'light'|'system'} theme
     */
    set(theme) {
        // Si Flux está disponible, actualizarlo también
        if (window.Flux && window.Flux.appearance !== undefined) {
            window.Flux.appearance = theme;
        }

        // Compatibilidad con implementaciones antiguas
        if (window.$flux && window.$flux.appearance !== undefined) {
            window.$flux.appearance = theme;
        }

        localStorage.setItem(this._fluxKey, theme);
        localStorage.setItem(this._key, theme);
        this.apply();
    },

    /**
     * Inicializa el listener para sincronización con Flux.
     */
    init() {
        // Escuchar cambios en Flux appearance
        let lastAppearance = this.getFluxAppearance();
        setInterval(() => {
            const currentAppearance = this.getFluxAppearance();
            if (currentAppearance !== lastAppearance) {
                lastAppearance = currentAppearance;
                this.apply();
            }
        }, 100);

        // Escuchar cambios en el sistema (para modo 'system')
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const appearance = this.getFluxAppearance() ?? localStorage.getItem(this._key);
            if (appearance === 'system') {
                this.apply();
            }
        });
    },
};

// ─────────────────────────────────────────────────────────────────────────────
// SECCIÓN 4 · FUNCIONES LEGACY (retrocompatibilidad)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Confirma la eliminación de un registro y despacha un evento Livewire.
 * Mantiene compatibilidad con las llamadas existentes en PowerGrid.
 *
 * Uso en PHP:
 *   ->attributes(['onclick' => "starchoDelete({$row->id}, 'Nombre', 'deleteRole', 'admin.roles-table')"])
 *
 * @param {number} recordId        ID del registro a eliminar.
 * @param {string} name            Nombre legible del registro (aparece en el mensaje).
 * @param {string} livewireEvent   Nombre del evento Livewire a despachar (p.ej. 'deleteRole').
 * @param {string} componentName   Nombre del componente destino   (p.ej. 'admin.roles-table').
 */
window.starchoDelete = function (recordId, name, livewireEvent, componentName) {
    const deleteMessageTemplate = tr('delete_message', 'Delete ":name"? This action cannot be undone.');

    confirm({
        title  : tr('delete_title', 'Confirm deletion'),
        message: deleteMessageTemplate.replace(':name', name),
        okText : tr('delete_ok', 'Yes, delete'),
        onConfirm() {
            Livewire.dispatchTo(componentName, livewireEvent, { id: recordId });
        },
    });
};

// ─────────────────────────────────────────────────────────────────────────────
// SECCIÓN 5 · COMPONENTE ALPINE — starchoApp()
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Componente Alpine principal del área /app.
 * Se registra como x-data="starchoApp([1,2])" en layouts/app/sidebar.blade.php,
 * donde el array es el resultado de json_encode($openMenuIds) desde PHP.
 *
 * Estado:
 *   - isDark          {boolean}   Tema oscuro activo.
 *   - sidebarCollapsed{boolean}   Sidebar en modo colapsado (icónico).
 *   - mobOpen         {boolean}   Sidebar abierto en móvil.
 *   - showLogout      {boolean}   Modal de confirmación de cierre de sesión.
 *   - search          {string}    Valor del campo de búsqueda en topbar.
 *   - openMenus       {number[]}  IDs de ítems de menú con submenu abierto.
 *
 * Métodos públicos:
 *   - toggleMenu(id)  Abre/cierra el submenu identificado por id.
 *   - init()          Llamado automáticamente por Alpine al montar.
 *
 * @param {number[]} [initialOpenMenus=[]]  IDs de submenús que deben abrirse al cargar
 *                                          (calculados server-side por PHP).
 */
window.starchoApp = function (initialOpenMenus) {
    return {
        // Usar el sistema unificado de tema de Starcho
        isDark          : window.Starcho.dark.isDark(),
        sidebarCollapsed: localStorage.getItem('starcho_collapsed') === 'true',
        mobOpen         : false,
        showLogout      : false,
        search          : '',
        /** Submenús abiertos — pre-poblado desde PHP para resaltar la ruta activa. */
        openMenus       : Array.isArray(initialOpenMenus) ? initialOpenMenus : [],

        /**
         * Ciclo de vida Alpine.
         * Persiste cambios reactivos en localStorage y gestiona el botón móvil.
         */
        init() {
            // Aplicar el tema usando el sistema unificado
            window.Starcho.dark.apply();

            // Inicializar sincronización con Flux
            window.Starcho.dark.init();

            // Persistir cambios usando Starcho.dark
            this.$watch('isDark', v => {
                window.Starcho.dark.set(v ? 'dark' : 'light');
            });
            this.$watch('sidebarCollapsed', v => localStorage.setItem('starcho_collapsed', v ? 'true'  : 'false'));

            /* Muestra/oculta el botón hamburger según el viewport. */
            const mobBtn   = document.getElementById('mobBtn');
            const checkMob = () => {
                if (mobBtn) mobBtn.style.display = window.innerWidth <= 800 ? 'flex' : 'none';
            };
            checkMob();
            window.addEventListener('resize', checkMob);

            /* Barra de progreso de navegación (NProgress-like) */
            const progress = document.createElement('div');
            progress.id = 'starcho-progress-bar';
            progress.style.position = 'fixed';
            progress.style.top = '0';
            progress.style.left = '0';
            progress.style.height = '4px';
            progress.style.width = '0%';
            progress.style.backgroundColor = 'var(--primary, #fe2c55)';
            progress.style.zIndex = '9999';
            progress.style.transition = 'width 0.2s ease, opacity 0.4s ease';
            progress.style.opacity = '0';
            document.body.appendChild(progress);

            const startProgress = () => {
                progress.style.opacity = '1';
                progress.style.width = '20%';
                setTimeout(() => { if (progress.style.width === '20%') progress.style.width = '64%'; }, 180);
            };

            const completeProgress = () => {
                progress.style.width = '100%';
                setTimeout(() => progress.style.opacity = '0', 220);
                setTimeout(() => progress.style.width = '0%', 500);
            };

            window.addEventListener('beforeunload', startProgress);
            window.addEventListener('pageshow', completeProgress);

            // Enrutamiento Livewire (opcionales push) y Turbolinks si existiera
            document.addEventListener('livewire:load', () => {
                document.addEventListener('livewire:beforeDomUpdate', startProgress);
                document.addEventListener('livewire:load', completeProgress);
            });
        },

        /**
         * Abre o cierra el submenu de un ítem de menú lateral.
         * @param {number} id  ID del StarchoMenuItem padre.
         */
        toggleMenu(id) {
            const idx = this.openMenus.indexOf(id);
            if (idx > -1) this.openMenus.splice(idx, 1);
            else           this.openMenus.push(id);
        },
    };
};

// ─────────────────────────────────────────────────────────────────────────────
// SECCIÓN 6 · EXPORTACIÓN GLOBAL
// ─────────────────────────────────────────────────────────────────────────────

/** Objeto global expuesto en window para uso desde blade/inline scripts. */
window.Starcho = { confirm, notify, alert, dark };
