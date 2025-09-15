<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/form/preferences_form.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/local/field_levels.php');

use mod_valuemapdoc\form\preferences_form;
use mod_valuemapdoc\local\field_levels;

// Parameters
$cmid = required_param('cmid', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_URL);

// Get course module and course
$cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$valuemapdoc = $DB->get_record('valuemapdoc', array('id' => $cm->instance), '*', MUST_EXIST);

// Security checks
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/valuemapdoc:view', $context);

// Set up page
$PAGE->set_url('/mod/valuemapdoc/preferences.php', array('cmid' => $cmid));
$PAGE->set_title(format_string($valuemapdoc->name) . ' - ' . get_string('field_level_preferences', 'mod_valuemapdoc'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);

// Set return URL if not provided
if (empty($returnurl)) {
    $returnurl = new moodle_url('/mod/valuemapdoc/view.php', array('id' => $cmid));
} else {
    $returnurl = new moodle_url($returnurl);
}

// Create form
$customdata = [
    'cmid' => $cmid,
    'returnurl' => $returnurl->out(false)
];

$form = new preferences_form(null, $customdata);

// Handle form submission
if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    
    // Save user preference
    $success = field_levels::set_user_level($data->field_level);
    
    if ($success) {
        // Success message
        $level_config = field_levels::get_user_level_config();
        $message = get_string('preferences_saved', 'mod_valuemapdoc') . ' ' . 
                   get_string('selected_level', 'mod_valuemapdoc') . ': ' . $level_config['name'];
        
        \core\notification::success($message);
    } else {
        // Error message
        \core\notification::error(get_string('preferences_not_saved', 'mod_valuemapdoc'));
    }
    
    redirect($returnurl);
}

// Output page
echo $OUTPUT->header();

// Breadcrumb navigation
$node = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
if ($node) {
    $node->make_active();
}

// Page heading
echo $OUTPUT->heading(get_string('field_level_preferences', 'mod_valuemapdoc'), 2);

// Additional information
echo html_writer::div(
    get_string('preferences_explanation', 'mod_valuemapdoc'),
    'alert alert-info'
);

// Display form
$form->display();
echo $OUTPUT->footer();