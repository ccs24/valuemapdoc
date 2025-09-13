<?php
require_once('../../config.php');
require_once('classes/form/entry_form.php');

$id = required_param('id', PARAM_INT); // course_module ID
$entryid = optional_param('entryid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$groupmode = groups_get_activity_groupmode($cm);
$groupid = groups_get_activity_group($cm);
if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
    $usergroups = groups_get_user_groups($course->id, $USER->id);
    if (!in_array($groupid, $usergroups[0])) {
        throw new moodle_exception('nopermission', 'mod_valuemapdoc');
        }
}

$savetocopy = false;


if ($entryid) {
    // Edycja istniejącego wpisu
    $entry = $DB->get_record('valuemapdoc_entries', ['id' => $entryid], '*', MUST_EXIST);
    $entry->id = $id;
    $entry->entryid = $entryid;
//    $entry->instance = $cm->instance;
    $entry->cid = $cm->id;

    if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
        if ($entry->groupid != $groupid && !$entry->ismaster) {
            throw new moodle_exception('nopermission', 'mod_valuemapdoc');
        }
    }

    if ($entry->ismaster && !$valuemapdoc->ismaster) {
        // Użytkownik edytuje rekord masterowy w instancji nie-master — twórz kopię
        $savetocopy = true;
    } 
    $entry->ismaster = $valuemapdoc->ismaster;
    
} else {
    // Tworzenie nowego wpisu – pusty formularz
    $entry = new stdClass();
    $entry->id = $id;
    $entry->entryid = 0;
//    $entry->instance = $cm->instance;
    $entry->cid = $cm->id;
    $entry->ismaster = $valuemapdoc->ismaster;
}
$mform = new valuemapdoc_entry_form(null, [
    'id' => $id,
    'savetocopy' => $savetocopy,
    ]);
    
$mform->set_data($entry);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/valuemapdoc/view.php', [
        'id' => $id    ]));

} else if ($mform->is_submitted() && optional_param('saveasnew', null, PARAM_RAW) !== null) {
//} else if ($mform->is_submitted() && $mform->get_submit_value('saveasnew')) {
    $data = $mform->get_data();

    $data->id = null;
    $data->entryid = null;
    $data->userid = $USER->id;
    $data->course = $course->id;
//    $data->instance = $cm->instance;
    $data->groupid = groups_get_activity_group($cm);
    $data->ismaster = $valuemapdoc->ismaster;
    $data->cid = $cm->id;
    $data->timecreated = time();
    $data->timemodified = time();

    $DB->insert_record('valuemapdoc_entries', $data);

    redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]), get_string('entrysavedasnew', 'mod_valuemapdoc'));

} else if ($data = $mform->get_data()) {
    $data->timemodified = time();
        if ($savetocopy) {
            // Użytkownik edytuje rekord masterowy w instancji nie-master — twórz kopię
            unset($data->entryid);
            unset($data->id);
        }
    
    $data->userid = $USER->id;
    $data->course = $course->id;
//    $data->instance = $cm->instance;
    $data->groupid = groups_get_activity_group($cm);
    $data->ismaster = $valuemapdoc->ismaster;
    $data->cid = $cm->id;

    if (!empty($data->entryid)) {// && $valuemapdoc->ismaster) {
        // Aktualizacja istniejącego wpisu w masterze
        $data->id = $data->entryid;
        $DB->update_record('valuemapdoc_entries', $data);
    } else {
        // Tworzenie nowego wpisu lub kopia wpisu z mastera
        $data->ismaster = $valuemapdoc->ismaster;
        $DB->insert_record('valuemapdoc_entries', $data);
    }

    redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]), get_string('entryupdated', 'valuemapdoc'));
} else {
    $PAGE->set_url('/mod/valuemapdoc/edit.php', ['id' => $id, 'entryid' => $entryid]);
    $PAGE->set_title(get_string('pluginname', 'valuemapdoc'));
    $PAGE->set_heading($course->fullname);
    $PAGE->set_context($context);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('editentry', 'valuemapdoc'));
    if ($savetocopy) {
        echo $OUTPUT->notification(get_string('masterentrycopyinfo', 'valuemapdoc'), 'notifyproblem');
    }   
    $mform->display();
    echo $OUTPUT->footer();
}
