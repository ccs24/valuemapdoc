<?php
require_once('../../config.php');
require_once('classes/form/entry_form.php');

$id = required_param('id', PARAM_INT); // course_module ID
$entryids = optional_param('entryids', '', PARAM_TEXT); // np. "13,14,15"
$entryidarray = array_filter(array_map('intval', explode(',', $entryids)));

//var_dump($id, $entryids);
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

    // Tworzenie nowego wpisu – pusty formularz
    $mform = new valuemapdoc_entry_form(null, [
        'id' => $id,
        'savetocopy' => false,
    ]);
    


    $entry = new stdClass();
    $entry->id = $id;
//    $entry->entryid = 0;
    $entry->entryids = $entryids;
    $entry->cid = $cm->id;
    $entry->ismaster = $valuemapdoc->ismaster;

    $mform->set_data($entry);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/valuemapdoc/view.php', [
        'id' => $id    ]));

} else if ($mform->is_submitted() && ($data = $mform->get_data())) {

    $data = $mform->get_data();
    $data->id = null;
//    $data->instance = $cm->instance;
    $data->groupid = groups_get_activity_group($cm);
    $data->ismaster = $valuemapdoc->ismaster;
    $data->cid = $cm->id;
    $data->timemodified = time();

foreach ($entryidarray as $entryid) {
    if (is_numeric($entryid)) {
            // Edycja istniejącego wpisu
            $entry = $DB->get_record('valuemapdoc_entries', ['id' => $entryid], '*', MUST_EXIST);

            if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
                if ($entry->groupid != $groupid && !$entry->ismaster) {
                    continue; // Użytkownik nie ma dostępu do wpisu w innym grupie
                }
            }

            $columns = [
    'market', 'industry', 'role', 'businessgoal', 'strategy', 'difficulty', 'situation',
    'statusquo', 'coi', 'differentiator', 'impact', 'newstate', 'successmetric',
    'impactstrategy', 'impactbusinessgoal', 'impactothers', 'proof', 'time2results',
    'quote', 'clientname'
];
            foreach ($columns as $column) {
                if (isset($data->$column) && trim($data->$column) !== '') {
                    $entry->$column = $data->$column;
                }
            }

            if($entry->ismaster == 1 && $valuemapdoc->ismaster != 1) {
                continue; // Nie pozwalamy na edycję wpisów master, jeśli moduł nie jest ustawiony jako master
            }
            $entry->timemodified = time();
        $DB->update_record('valuemapdoc_entries', $entry);


    }
}




    redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]), get_string('entrysavedasnew', 'mod_valuemapdoc'));


}  else {
    $PAGE->set_url('/mod/valuemapdoc/edit_bulk.php', ['id' => $id, 'entryids' => $entryids]);
    $PAGE->set_title(get_string('pluginname', 'valuemapdoc'));
    $PAGE->set_heading($course->fullname);
    $PAGE->set_context($context);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('editentry', 'valuemapdoc'));
    echo $OUTPUT->notification('Edytujesz ' . count($entryidarray) . ' rekordów!', 'notifyproblem');
    
    $mform->display();
    echo $OUTPUT->footer();
}
