<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/form/entry_form.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/local/field_levels.php');

use mod_valuemapdoc\local\field_levels;

// Parameters
$id = required_param('id', PARAM_INT);
$entryids = required_param('entryids', PARAM_TEXT);

// Convert entryids from comma-separated string to array
$entryids_array = array_filter(array_map('intval', explode(',', $entryids)));

if (empty($entryids_array)) {
    print_error('noentriesselected', 'mod_valuemapdoc');
}

// Get course module and course
$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$valuemapdoc = $DB->get_record('valuemapdoc', array('id' => $cm->instance), '*', MUST_EXIST);

// Security checks
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/valuemapdoc:manageentries', $context); 

// Set up page
$PAGE->set_url('/mod/valuemapdoc/edit_bulk.php', array(
    'id' => $id,
    'entryids' => $entryids
));
$PAGE->set_title(format_string($valuemapdoc->name) . ' - ' . get_string('bulk_edit', 'mod_valuemapdoc'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);

// Get entries to be edited
$entries = [];
if (!empty($entryids_array)) {
    list($insql, $inparams) = $DB->get_in_or_equal($entryids_array);//, SQL_PARAMS_NUMBERED);
    $sql = "SELECT * FROM {valuemapdoc_entries} WHERE id $insql AND cid = ?";
    $params = array_merge($inparams, [$cm->id]);
    $entries = $DB->get_records_sql($sql, $params);
}

if (empty($entries)) {
    print_error('noentriesfound', 'mod_valuemapdoc');
}

// Create form with bulk mode enabled
$customdata = [
    'bulk_mode' => true,
    'entries_count' => count($entries),
    'entryids' => $entryids,
    'cmid' => $cm->id,
    'id' => $id
];

$returnurl = new moodle_url('/mod/valuemapdoc/view.php', array('id' => $id));
$form = new valuemapdoc_entry_form(null, $customdata);

// Set entryids in form data
$form->set_data(['entryids' => $entryids]);

// Handle form submission
if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($bulk_data = $form->get_bulk_data()) {
    
    // Check if there's actually data to update
    if (empty($bulk_data)) {
        \core\notification::warning(get_string('no_changes_made', 'mod_valuemapdoc'));
        redirect($returnurl);
    }
    
    // Start transaction
    $transaction = $DB->start_delegated_transaction();
    
    try {
        $updated_count = 0;
        
        foreach ($entries as $entry) {
            $update_record = new stdClass();
            $update_record->id = $entry->id;
            $update_record->timemodified = time();
            
            // Add only the fields that have data (non-empty)
            foreach ($bulk_data as $field => $value) {
                $update_record->$field = $value;
            }
            
            $DB->update_record('valuemapdoc_entries', $update_record);
            $updated_count++;
        }
        
        // Commit transaction
        $transaction->allow_commit();
        
        // Success message
        $success_data = [
            'count' => $updated_count,
            'fields' => implode(', ', array_keys($bulk_data))
        ];
        $message = get_string('bulk_update_success', 'mod_valuemapdoc', $success_data);
        \core\notification::success($message);
        
        redirect($returnurl);
        
    } catch (Exception $e) {
        // Rollback transaction
        $transaction->rollback($e);
        
        // Error message
        \core\notification::error(get_string('bulk_update_error', 'mod_valuemapdoc') . ': ' . $e->getMessage());
    }
}

// Output page
echo $OUTPUT->header();

// Breadcrumb navigation
$node = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
if ($node) {
    $node->make_active();
}

// Page heading
echo $OUTPUT->heading(get_string('bulk_edit', 'mod_valuemapdoc'), 2);

// Show current field level information
$user_level_config = field_levels::get_user_level_config();
$level_info_data = [
    'level_name' => $user_level_config['name'],
    'fields_count' => $user_level_config['fields_count']
];
$level_info = get_string('bulk_editing_at_level', 'mod_valuemapdoc', $level_info_data);

echo html_writer::div($level_info, 'alert alert-info');

// Display form
$form->display();


echo $OUTPUT->footer();