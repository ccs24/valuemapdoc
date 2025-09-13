<?php
require_once('../../config.php');
require_once('classes/local/session_helper.php');

use mod_valuemapdoc\local\session_helper;

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);
require_login($cm->course, true, $cm);

$groupmode = groups_get_activity_groupmode($cm);
$groupid = groups_get_activity_group($cm);
if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
    $usergroups = groups_get_user_groups($cm->course, $USER->id);
    if (!in_array($groupid, $usergroups[0])) {
        throw new moodle_exception('nopermission', 'mod_valuemapdoc');
    }
}

$PAGE->set_context($context);
//$PAGE->set_url(new moodle_url('/mod/valuemapdoc/save_content.php', ['id' => $id]));
//$PAGE->set_title(get_string('savecontent', 'mod_valuemapdoc'));
//$PAGE->set_heading(get_string('savecontent', 'mod_valuemapdoc'));

// Pobierz treść z sesji.
$data = session_helper::get();
// Sprawdź, czy treść jest pusta.
if (empty($data)) {
    throw new moodle_exception('nocontenttosave', 'mod_valuemapdoc');
}

global $DB;

// 'entryids' => $data->entryids -> pomysł na połączenie z inną tabelą

$record = [
'courseid' => $COURSE->id,
'cmid' => $id,
'userid' => $USER->id,
'groupid' => $groupid,
'templateid' => $data['templateid'],
'customprompt' => $data['customprompt'],
'opportunityname' => $data['opportunityname'],
'content' => $data['workingcontent']['text'],
//'effectiveness' => $data['effectiveness'],
//'feedback' => $data['feedback'],
'timecreated' => time(),
];
$docid = $DB->insert_record('valuemapdoc_content', $record);
// Wyczyść dane z sesji po zapisaniu.
session_helper::clear();

// Przekierowanie
redirect(
    new moodle_url('/mod/valuemapdoc/rate_content.php', ['id' => $id, 'docid' => $docid]),
    get_string('documentsaved', 'mod_valuemapdoc'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);