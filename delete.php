<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT);         // Course module ID
$entryid = required_param('entryid', PARAM_INT); // Entry ID

require_sesskey();
// Sprawdź, czy użytkownik ma uprawnienie do usunięcia rekordu
require_capability('mod/valuemapdoc:manageentries', $context);

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
require_login($course, false, $cm);
$context = context_module::instance($cm->id);

// Sprawdź czy rekord istnieje
// Sprawdź czy rekord istnieje
$entry = $DB->get_record('valuemapdoc_entries', ['id' => $entryid], '*', MUST_EXIST);

// Weryfikacja przynależności do grupy w trybie oddzielnych grup
$groupmode = groups_get_activity_groupmode($cm);
if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
    $usergroups = groups_get_user_groups($course->id, $USER->id);
    if (!in_array($entry->groupid, $usergroups[0])) {
        throw new required_capability_exception($context, 'mod/valuemapdoc:manageentries', 'nopermissions', '');
    }
}

// (Opcjonalnie) sprawdź, czy użytkownik jest autorem lub ma uprawnienie

// Usuń rekord
$DB->delete_records('valuemapdoc_entries', ['id' => $entryid]);

redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]), get_string('entrydeleted', 'mod_valuemapdoc'));