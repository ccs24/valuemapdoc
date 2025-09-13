<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/local/field_levels.php');

use mod_valuemapdoc\local\field_levels;

$id = optional_param('id', 0, PARAM_INT);
$readonly = optional_param('readonly', -1, PARAM_INT);
$selectedfilter = optional_param('filtercmid', '', PARAM_TEXT);
$selectedgroupid = optional_param('groupid', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $valuemapdoc = $DB->get_record('valuemapdoc', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('missingidandcmid', 'mod_valuemapdoc');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/valuemapdoc/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($valuemapdoc->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Get user's field level and filter columns accordingly
$user_fields = field_levels::get_user_fields();
$user_level_config = field_levels::get_user_level_config();

// Pobierz preferencje użytkownika TUTAJ
// $user_preference = get_user_preference('mod_valuemapdoc_field_level', 'basic');
// $user_level = field_levels::get_user_level_from_preference($user_preference);



// Define all possible columns
$all_columns = [
    'market', 'industry', 'role', 'businessgoal', 'strategy', 'difficulty',
    'situation', 'statusquo', 'coi', 'differentiator', 'impact', 'newstate',
    'successmetric', 'impactstrategy', 'impactbusinessgoal', 'impactothers',
    'proof', 'time2results', 'quote', 'clientname'
];

// Filter columns based on user's level
$columns = array_intersect($all_columns, $user_fields);

// Handle readonly mode
$readonly_forced = !has_capability('mod/valuemapdoc:manageentries', $context);
$can_toggle_readonly = has_capability('mod/valuemapdoc:manageentries', $context);

// If readonly parameter is set and user can toggle, use it
if ($readonly !== -1 && $can_toggle_readonly) {
    $readonly_mode = (bool)$readonly;
    set_user_preferences('mod_valuemapdoc_readonly_' . $cm->id, $readonly_mode);
} else {
    // Otherwise use saved preference or default based on permissions
    $readonly_mode = get_user_preferences('mod_valuemapdoc_readonly_' . $cm->id, $readonly_forced);
}

// Force readonly if user doesn't have manage capability
if ($readonly_forced) {
    $readonly_mode = true;
}

// Get groups data
$groups = groups_get_all_groups($course->id);
$haaccessallgroups = has_capability('moodle/site:accessallgroups', $context);
$groupoptions = [];

if ($haaccessallgroups || !empty($groups)) {
    $groupoptions[0] = get_string('allgroups', 'mod_valuemapdoc');
    
    if (!empty($groups)) {
        foreach ($groups as $group) {
            $groupoptions[$group->id] = $group->name;
        }
    }
}

// Master instances for filtering
$sql = "SELECT cm.id as cmid, c.shortname, v.name
        FROM {course_modules} cm
        JOIN {valuemapdoc} v ON cm.instance = v.id
        JOIN {course} c ON cm.course = c.id
        WHERE cm.module = (SELECT id FROM {modules} WHERE name = 'valuemapdoc')
          AND v.ismaster = 1
          AND c.id = ?
        ORDER BY c.shortname, v.name";

$masterinstances = $DB->get_records_sql($sql, [$course->id]);

/*$options = ['' => get_string('nomasterfilter', 'mod_valuemapdoc')];
foreach ($masterinstances as $instance) {
    $options[$instance->cmid] = $instance->shortname . ': ' . $instance->name;
}
    */
$options = [];
foreach ($masterinstances as $instance) {
    $options[] = (object)[
        'key' => $instance->cmid,
        'label' => $instance->shortname . ': ' . $instance->name,
        'selected' => ($selectedfilter == $instance->cmid)
    ];
}

//var_dump($options);
//die();


$classmaster = '';
if (!empty($selectedfilter)) {
    $classmaster = 'selected-master';
}

// Get markets data
$marketsurl = new moodle_url('/mod/valuemapdoc/markets.php', ['id' => $id]);
$markets = [];

// Get content entries
$entries = [];
// ... (your existing entries loading code)

// Templates
$templates = $DB->get_records('valuemapdoc_templates', null, 'templatetype, name');
$templateselect = [];
$grouped_templates = [];

foreach ($templates as $template) {
    $templateselect[$template->id] = $template->templatetype . ': ' . $template->name;
    $grouped_templates[$template->templatetype][] = $template;
}

$columnsjson = json_encode(array_map(function($c) {
        return [
            'title' => get_string($c, 'mod_valuemapdoc'),
            'field' => $c,
            'hozAlign' => 'left',
            'headerSort' => true,
            'width' => 150
        ];
    }, $columns), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);



// Prepare data for template
$tablecontent = [
    'contextid' => $context->id,
    'cmid' => $cm->id,
    'courseid' => $course->id,
    'name' => format_string($valuemapdoc->name),
    'intro' => format_module_intro('valuemapdoc', $valuemapdoc, $cm->id),
    'has_intro' => !empty($valuemapdoc->intro),
    'can_add' => !$readonly_mode,
    'can_edit' => !$readonly_mode,
    'can_delete' => !$readonly_mode && has_capability('mod/valuemapdoc:manageentries', $context),
    'can_generate' => true,
    'selectedfilter' => $selectedfilter,
    'filtercmid' => $selectedfilter,
    'add_url' => new moodle_url('/mod/valuemapdoc/edit.php', ['id' => $id]),
    'export_url' => new moodle_url('/mod/valuemapdoc/export.php', ['id' => $id]),
    'classmaster' => $classmaster,
    'masteroptions' => $options,
    
    // Dynamic columns based on user's field level
    'columns' => $columnsjson,
    'tabulatordata' => [
        'courseid' => $course->id,
        'cmid' => $cm->id,
        'filtercmid' => $selectedfilter
    ],
    'documents' => [], // processed content entries
    'tempselect' => array_values($templateselect),
    'templates' => array_values($grouped_templates),
    'bulk_edit_url' => new moodle_url('/mod/valuemapdoc/edit_bulk.php', ['id' => $id]),
    'hasaccessallgroups' => $haaccessallgroups,
    'groupoptions' => $groupoptions,
    
    'has_markets' => !empty($markets),
    'readonly' => $readonly_mode,
    'markets' => array_values($markets),
    'readonly_toggle' => [
        'can_toggle' => $can_toggle_readonly,
        'current_readonly' => $readonly_mode,
        'is_user_choice' => !$readonly_forced,
        'toggle_url' => new moodle_url('/mod/valuemapdoc/view.php', [
            'id' => $cm->id,
            'readonly' => $readonly_mode ? 0 : 1
        ]),
        'show_toggle' => $can_toggle_readonly
    ],
    'readonly_info' => [
        'is_forced' => $readonly_forced,
        'is_user_choice' => !$readonly_forced && $readonly_mode,
        'can_edit' => true,
    ],
    
    // Field level information
    'field_level' => [
        'current_level' => field_levels::get_user_level(),
        'level_name' => $user_level_config['name'],
        'fields_count' => $user_level_config['fields_count'],
        'preferences_url' => new moodle_url('/mod/valuemapdoc/preferences.php', [
            'cmid' => $cm->id,
            'returnurl' => $PAGE->url->out(false)
        ])
    ]
];

//var_dump($tablecontent);    
//die();

$renderer = $PAGE->get_renderer('mod_valuemapdoc');
$PAGE->requires->js_call_amd('mod_valuemapdoc/tabulatormap', 'init', [
    'courseid' => $course->id,
    'cmid' => $cm->id,
    'filtercmid' => $selectedfilter,
    'columns' => $columns
]);

echo $OUTPUT->header();
echo $renderer->render_from_template('mod_valuemapdoc/view', $tablecontent);
echo $OUTPUT->footer();


