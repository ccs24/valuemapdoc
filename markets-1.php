<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/lib.php');

use mod_valuemapdoc\local\markets;
use mod_valuemapdoc\form\markets_form;

$id = required_param('id', PARAM_INT); // Course module ID
$action = optional_param('action', 'list', PARAM_ALPHA);
$marketid = optional_param('marketid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Check capability
require_capability('mod/valuemapdoc:addinstance', $context);

$PAGE->set_url('/mod/valuemapdoc/markets.php', ['id' => $id]);
$PAGE->set_title(format_string($valuemapdoc->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if ($action == 'delete' && $marketid) {
    require_sesskey();
    markets::delete($marketid);
    redirect($PAGE->url, get_string('marketdeleted', 'mod_valuemapdoc'));
}

// Handle actions
if ($action == 'add' || $action == 'edit') {
    $market = null;
    if ($action == 'edit' && $marketid) {
        $market = $DB->get_record('valuemapdoc_markets', ['id' => $marketid], '*', MUST_EXIST);
    }
    
    $customdata = [];

    $parents = [0 => get_string('none')];
    $all_records = markets::get_by_type($course->id, '');
    foreach ($all_records as $record) {
        if (!$market || $record->id != $market->id) {
            $parents[$record->id] = $record->name . ' (' . get_string('type_' . $record->type, 'mod_valuemapdoc') . ')';
        }
    }
    $customdata['parents'] = $parents;
    /*
    
    if ($action == 'add') {
        // For adding - determine what types are allowed and what parents are available
        $step = optional_param('step', '', PARAM_ALPHA);
        $parent_id = optional_param('parent', 0, PARAM_INT);
        
        if (empty($step)) {
            // Step 1: Add Market (no parent needed)
            $customdata['allowed_types'] = [markets::TYPE_MARKET];
            $customdata['parents'] = [0 => get_string('none')];
        } else {
            // Determine allowed types based on selected parent
            if ($parent_id > 0) {
                $parent = $DB->get_record('valuemapdoc_markets', ['id' => $parent_id]);
                $allowed_children = markets::get_allowed_children($parent->type);
                $customdata['allowed_types'] = $allowed_children;
                $customdata['parents'] = [$parent_id => $parent->name];
            }
        }
    } else {
        // For editing - get appropriate parents for current type
        $customdata['parents'] = markets::get_potential_parents($course->id, $market->type);
    }

    */
     // UTWORZ URL Z PARAMETRAMI
    $form_url = new moodle_url('/mod/valuemapdoc/markets.php', [
        'id' => $id,
        'action' => $action
    ]);

    if ($action == 'edit' && $marketid) {
        $form_url->param('marketid', $marketid);
    }
    
    error_log('FORM DEBUG: Form URL = ' . $form_url->out());
    
    $mform = new markets_form($form_url, $customdata);
    
    if ($market) {
        $mform->set_data($market);
    } else {
        $mform->set_data(['id' => $id, 'courseid' => $course->id, 'parentid' => 0]);
    }
    
    
    if ($mform->is_cancelled()) {
        error_log('MARKETS: Is cancelled');
        redirect($PAGE->url);
    } else if ($data = $mform->get_data()) {
        // Dodaj courseid do danych jeśli go nie ma
        //$data->courseid = $course->id;
        // DEBUGGING - dodaj te linie
        error_log('MARKETS DEBUG: Form data received');
        error_log('MARKETS DEBUG: Data = ' . print_r($data, true));
        
        // Dodaj courseid do danych jeśli go nie ma
        $data->courseid = $course->id;
        
        error_log('MARKETS DEBUG: Course ID set to: ' . $data->courseid);

        try {
            if ($action == 'edit') {
                error_log('MARKETS DEBUG: Attempting to update market ID: ' . $marketid);
                $result = markets::update($marketid, $data);
                error_log('MARKETS DEBUG: Update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
                $message = get_string('marketupdated', 'mod_valuemapdoc');
            } else {
                error_log('MARKETS DEBUG: Attempting to create new market');
                $new_id = markets::create($data);
                error_log('MARKETS DEBUG: Create result - New ID: ' . $new_id);
                $message = get_string('marketcreated', 'mod_valuemapdoc');
            }
            
            error_log('MARKETS DEBUG: Redirecting with message: ' . $message);
            redirect($PAGE->url, $message);
            
        } catch (Exception $e) {
            error_log('MARKETS DEBUG: Exception caught: ' . $e->getMessage());
            error_log('MARKETS DEBUG: Exception trace: ' . $e->getTraceAsString());
            throw $e;
        }
        
    } else { // Form not submitted or validation failed
        error_log('MARKETS DEBUG: Data = ' . print_r($data, true));
        error_log('MARKETS DEBUG: Form not submitted or validation failed');
        if ($mform->is_submitted()) {
            error_log('MARKETS DEBUG: Form was submitted but validation failed');
            $errors = $mform->get_errors();
            error_log('MARKETS DEBUG: Validation errors: ' . print_r($errors, true));
        }
    }
    
    echo $OUTPUT->header();
    echo $OUTPUT->heading($action == 'edit' ? get_string('editmarket', 'mod_valuemapdoc') : get_string('addmarket', 'mod_valuemapdoc'));
    $mform->display();
    echo $OUTPUT->footer();
    exit;
}

// Default: show list
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('markets', 'mod_valuemapdoc'));

// Add button
$addurl = new moodle_url($PAGE->url, ['action' => 'add']);
echo $OUTPUT->single_button($addurl, get_string('addmarket', 'mod_valuemapdoc'), 'get');

// List markets
$markets_list = markets::get_by_type($course->id, '');

if (empty($markets_list)) {
    echo $OUTPUT->notification(get_string('nomarkets', 'mod_valuemapdoc'));
} else {
    $table = new html_table();
    $table->head = [
        get_string('markettype', 'mod_valuemapdoc'),
        get_string('marketname', 'mod_valuemapdoc'),
        get_string('marketdescription', 'mod_valuemapdoc'),
        get_string('actions')
    ];
    
    foreach ($markets_list as $market) {
        $editurl = new moodle_url($PAGE->url, ['action' => 'edit', 'marketid' => $market->id]);
        $deleteurl = new moodle_url($PAGE->url, ['action' => 'delete', 'marketid' => $market->id, 'sesskey' => sesskey()]);
        
        $actions = html_writer::link($editurl, get_string('edit')) . ' | ' .
                  html_writer::link($deleteurl, get_string('delete'), 
                                  ['onclick' => 'return confirm("' . get_string('confirmdeletemarket', 'mod_valuemapdoc') . '")']);
        
        $table->data[] = [
            get_string('type_' . $market->type, 'mod_valuemapdoc'),
            format_string($market->name),
            format_text($market->description),
            $actions
        ];
    }
    
    echo html_writer::table($table);
}

echo $OUTPUT->footer();