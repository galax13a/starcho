<?php

return [

    // Página
    'page_title'    => 'Contactos',
    'page_subtitle' => 'Gestiona tus leads, prospectos y clientes.',
    'new_contact'   => 'Nuevo Contacto',
    'export_excel'  => 'Exportar Excel',
    'import_excel'  => 'Importar Excel',

    // Tarjetas de estadísticas
    'stat_total'      => 'Total',
    'stat_leads'      => 'Leads',
    'stat_prospects'  => 'Prospectos',
    'stat_customers'  => 'Clientes',
    'stat_churned'    => 'Perdidos',
    'stat_with_email' => 'Con Email',

    // Filtros tabla
    'filter_all_statuses' => 'Todos los estados',

    // Columnas tabla
    'col_id'      => 'ID',
    'col_name'    => 'Nombre',
    'col_company' => 'Empresa',
    'col_email'   => 'Email',
    'col_phone'   => 'Teléfono',
    'col_status'  => 'Estado',
    'col_active'  => 'Activo',
    'col_created' => 'Creado',
    'col_actions' => 'Acciones',

    // Modal
    'modal_subtitle'   => 'Sistema de gestión de contactos',
    'modal_title_new'  => 'Nuevo',
    'modal_title_edit' => 'Editar',
    'modal_contact'    => 'Contacto',

    // Campos
    'field_name'       => 'Nombre',
    'field_name_ph'    => 'Nombre completo…',
    'field_company'    => 'Empresa',
    'field_company_ph' => 'Empresa del contacto…',
    'field_email'      => 'Email',
    'field_email_ph'   => 'email@ejemplo.com',
    'field_phone'      => 'Teléfono',
    'field_phone_ph'   => '+34 600 000 000',
    'field_status'     => 'Estado',
    'field_active'     => 'Activo',
    'field_notes'      => 'Notas',
    'field_notes_ph'   => 'Notas sobre este contacto…',

    // Opciones de estado
    'status_lead'     => 'Lead',
    'status_prospect' => 'Prospecto',
    'status_customer' => 'Cliente',
    'status_churned'  => 'Perdido',
    'active_yes'      => 'Activo',
    'active_no'       => 'Inactivo',

    // Botones
    'btn_cancel' => 'Cancelar',
    'btn_save'   => 'Crear Contacto',
    'btn_update' => 'Actualizar Contacto',
    'btn_saving' => 'Guardando…',
    'import_title' => 'Importar Contactos',
    'import_subtitle' => 'Sube un Excel exportado previamente para crear o actualizar contactos.',
    'import_label' => 'Archivo Excel',
    'import_help' => 'Se usan las columnas id, name, company, email, phone, status, active y notes.',
    'import_cta' => 'Importar Contactos',
    'importing' => 'Importando…',
    'import_result' => 'Importacion completada: :created creados, :updated actualizados.',
    'import_error' => 'No se pudo importar el archivo de contactos.',
    'bulk_selected' => 'seleccionados',
    'bulk_delete_selected' => 'Eliminar seleccionados',
    'bulk_clear_selection' => 'Limpiar seleccion',
    'bulk_delete_confirm' => 'Los contactos seleccionados se eliminaran. Esta accion no se puede deshacer.',

    'notify' => [
        'created' => 'Contacto creado correctamente.',
        'updated' => 'Contacto actualizado correctamente.',
        'deleted' => 'Contacto eliminado correctamente.',
        'not_found' => 'El contacto no existe o no tienes permiso para acceder.',
        'no_selection' => 'Selecciona al menos un contacto.',
        'bulk_deleted' => 'Se eliminaron :count contactos correctamente.',
    ],

];
