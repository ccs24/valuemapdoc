<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/mod/valuemapdoc/lib.php');

require_once('classes/local/template_helper.php');

use mod_valuemapdoc\local\template_helper;


$id = required_param('id', PARAM_INT);        // Course module ID
$docid = required_param('docid', PARAM_INT);   // Document ID
$confirm = optional_param('confirm', 0, PARAM_BOOL);

// Sprawdź course module
$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$valuemapdoc = $DB->get_record('valuemapdoc', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
require_sesskey();

$context = context_module::instance($cm->id);
require_capability('mod/valuemapdoc:generatedocument', $context);

$document = $DB->get_record('valuemapdoc_content', ['id' => $docid], '*', MUST_EXIST);

// Weryfikacja przynależności do grupy w trybie oddzielnych grup
$groupmode = groups_get_activity_groupmode($cm);
if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
    $usergroups = groups_get_user_groups($course->id, $USER->id);
    if (!in_array($document->groupid, $usergroups[0])) {
        throw new required_capability_exception($context, 'mod/valuemapdoc:manageentries', 'nopermissions', '');
    }
}

// URLs
$return_url = new moodle_url('/mod/valuemapdoc/view.php', [
    'id' => $id
],'content-tab');
$confirm_url = new moodle_url('/mod/valuemapdoc/delete_content.php', [
    'id' => $id,
    'docid' => $docid,
    'confirm' => 1,
    'sesskey' => sesskey()
]);

// Jeśli potwierdzenie, usuń dokument
if ($confirm) {
    try {
        // Usuń rekord z bazy
        $DB->delete_records('valuemapdoc_content', ['id' => $docid]);
                
        //\core\notification::success(get_string('contentdeletedsuccess', 'mod_valuemapdoc'));
        redirect($return_url);
        
    } catch (Exception $e) {
       // \core\notification::error(get_string('contentdeletionfailed', 'mod_valuemapdoc'));
        redirect($return_url);
    }
}

//var_dump($document); // Debugging - usuń w produkcji
//die();
// Wyświetl stronę potwierdzenia
$PAGE->set_url('/mod/valuemapdoc/delete_content.php', ['id' => $id, 'docid' => $docid]);
$PAGE->set_title(format_string($valuemapdoc->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('deletecontent', 'mod_valuemapdoc'));

// Pokaż informacje o dokumencie
echo $OUTPUT->box_start('generalbox');

// Szczegóły dokumentu
echo html_writer::start_tag('dl', ['class' => 'row']);
echo html_writer::tag('dt', get_string('name'), ['class' => 'col-sm-3']);
echo html_writer::tag('dd', $document->name, ['class' => 'col-sm-9']);
$templateinfo = \mod_valuemapdoc\template_helper::get_template_fields_by_id($document->templateid);

echo html_writer::tag('dt', get_string('templatename', 'mod_valuemapdoc'), ['class' => 'col-sm-3']);
echo html_writer::tag('dd', $templateinfo['name'], ['class' => 'col-sm-9']);

echo html_writer::tag('dt', get_string('templatetype', 'mod_valuemapdoc'), ['class' => 'col-sm-3']);
echo html_writer::tag('dd', $templateinfo['templatetype'], ['class' => 'col-sm-9']);

echo html_writer::tag('dt', get_string('documentpreview', 'mod_valuemapdoc'), ['class' => 'col-sm-3']);
echo html_writer::tag('dd', format_string($document->content ?? get_string('untitled', 'mod_valuemapdoc')), ['class' => 'col-sm-9']);


echo html_writer::end_tag('dl');

echo $OUTPUT->box_end();

// Przyciski potwierdzenia
echo $OUTPUT->confirm(
    get_string('deletecontent', 'mod_valuemapdoc') . "?",
    $confirm_url,
    $return_url,
    [
        'type' => 'danger',
        'primarylabel' => get_string('delete'),
        'secondarylabel' => get_string('cancel')
    ]
);

echo $OUTPUT->footer();