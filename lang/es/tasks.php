<?php

return [

    // Página
    'page_title'    => 'Mis Tareas',
    'page_subtitle' => 'Gestiona tus tareas personales y de equipo.',
    'new_task'      => 'Nueva Tarea',
    'export_excel'  => 'Exportar Excel',
    'import_excel'  => 'Importar Excel',

    // Tarjetas de estadísticas
    'stat_total'       => 'Total',
    'stat_pending'     => 'Pendientes',
    'stat_in_progress' => 'En progreso',
    'stat_completed'   => 'Completadas',
    'stat_overdue'     => 'Vencidas',
    'stat_due_today'   => 'Vencen hoy',

    // Filtros tabla
    'filter_all_statuses'   => 'Todos los estados',
    'filter_all_priorities' => 'Todas las prioridades',

    // Columnas tabla
    'col_id'          => 'ID',
    'col_title'       => 'Título',
    'col_status'      => 'Estado',
    'col_priority'    => 'Prioridad',
    'col_due_date'    => 'Vencimiento',
    'col_assigned_to' => 'Asignado a',
    'col_created'     => 'Creado',
    'col_actions'     => 'Acciones',

    // Modal
    'modal_subtitle'   => 'Sistema de gestión de tareas',
    'modal_title_new'  => 'Nueva',
    'modal_title_edit' => 'Editar',
    'modal_task'       => 'Tarea',

    // Campos
    'field_title'      => 'Título',
    'field_title_ph'   => 'Nombre de la tarea…',
    'field_desc'       => 'Descripción',
    'field_desc_ph'    => 'Descripción opcional…',
    'field_status'     => 'Estado',
    'field_priority'   => 'Prioridad',
    'field_due_date'   => 'Fecha de vencimiento',
    'field_assign'     => 'Asignar a',
    'field_unassigned' => 'Sin asignar',

    // Opciones de estado
    'status_pending'     => 'Pendiente',
    'status_in_progress' => 'En progreso',
    'status_completed'   => 'Completada',
    'status_cancelled'   => 'Cancelada',

    // Opciones de prioridad
    'priority_low'    => 'Baja',
    'priority_medium' => 'Media',
    'priority_high'   => 'Alta',
    'priority_urgent' => 'Urgente',

    // Botones
    'btn_cancel' => 'Cancelar',
    'btn_save'   => 'Crear Tarea',
    'btn_update' => 'Actualizar Tarea',
    'btn_saving' => 'Guardando…',
    'import_title' => 'Importar Tareas',
    'import_subtitle' => 'Sube un Excel exportado previamente para crear o actualizar tareas.',
    'import_label' => 'Archivo Excel',
    'import_help' => 'Se usan las columnas id, title, description, status, priority, due_date y assigned_email.',
    'import_cta' => 'Importar Tareas',
    'importing' => 'Importando…',
    'import_result' => 'Importacion completada: :created creadas, :updated actualizadas.',
    'import_error' => 'No se pudo importar el archivo de tareas.',
    'bulk_selected' => 'seleccionadas',
    'bulk_delete_selected' => 'Eliminar seleccionadas',
    'bulk_clear_selection' => 'Limpiar seleccion',
    'bulk_delete_confirm' => 'Las tareas seleccionadas se eliminaran. Esta accion no se puede deshacer.',

    'notify' => [
        'created' => 'Tarea creada correctamente.',
        'updated' => 'Tarea actualizada correctamente.',
        'deleted' => 'Tarea eliminada correctamente.',
        'not_found' => 'La tarea no existe o no tienes permiso para acceder.',
        'no_selection' => 'Selecciona al menos una tarea.',
        'bulk_deleted' => 'Se eliminaron :count tareas correctamente.',
    ],

];
