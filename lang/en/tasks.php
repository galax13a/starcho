<?php

return [

    // Page
    'page_title'    => 'My Tasks',
    'page_subtitle' => 'Manage your personal and team tasks.',
    'new_task'      => 'New Task',
    'export_excel'  => 'Export Excel',
    'import_excel'  => 'Import Excel',

    // Stat cards
    'stat_total'       => 'Total',
    'stat_pending'     => 'Pending',
    'stat_in_progress' => 'In Progress',
    'stat_completed'   => 'Completed',
    'stat_overdue'     => 'Overdue',
    'stat_due_today'   => 'Due Today',

    // Table filters
    'filter_all_statuses'   => 'All statuses',
    'filter_all_priorities' => 'All priorities',

    // Table columns
    'col_id'          => 'ID',
    'col_title'       => 'Title',
    'col_status'      => 'Status',
    'col_priority'    => 'Priority',
    'col_due_date'    => 'Due date',
    'col_assigned_to' => 'Assigned to',
    'col_created'     => 'Created',
    'col_actions'     => 'Actions',

    // Modal
    'modal_subtitle'   => 'Task management system',
    'modal_title_new'  => 'New',
    'modal_title_edit' => 'Edit',
    'modal_task'       => 'Task',

    // Fields
    'field_title'      => 'Title',
    'field_title_ph'   => 'Task name…',
    'field_desc'       => 'Description',
    'field_desc_ph'    => 'Optional description…',
    'field_status'     => 'Status',
    'field_priority'   => 'Priority',
    'field_due_date'   => 'Due Date',
    'field_assign'     => 'Assign to',
    'field_unassigned' => 'Unassigned',

    // Status options
    'status_pending'     => 'Pending',
    'status_in_progress' => 'In Progress',
    'status_completed'   => 'Completed',
    'status_cancelled'   => 'Cancelled',

    // Priority options
    'priority_low'    => 'Low',
    'priority_medium' => 'Medium',
    'priority_high'   => 'High',
    'priority_urgent' => 'Urgent',

    // Buttons
    'btn_cancel' => 'Cancel',
    'btn_save'   => 'Create Task',
    'btn_update' => 'Update Task',
    'btn_saving' => 'Saving…',
    'import_title' => 'Import Tasks',
    'import_subtitle' => 'Upload a previously exported spreadsheet to create or update tasks.',
    'import_label' => 'Excel file',
    'import_help' => 'Uses the id, title, description, status, priority, due_date and assigned_email columns.',
    'import_cta' => 'Import Tasks',
    'importing' => 'Importing…',
    'import_result' => 'Import finished: :created created, :updated updated.',
    'import_error' => 'The tasks file could not be imported.',

    'notify' => [
        'created' => 'Task created successfully.',
        'updated' => 'Task updated successfully.',
        'deleted' => 'Task deleted successfully.',
        'not_found' => 'The task does not exist or you do not have permission.',
    ],

];
