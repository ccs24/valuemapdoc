<?php
require_once('../../config.php');
require_once('classes/form/edit_content_form.php');
require_once('classes/local/session_helper.php');

use mod_valuemapdoc\local\session_helper;

$id = required_param('id', PARAM_INT);
$docid = required_param('docid', PARAM_INT);   // Document ID

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/valuemapdoc/edit_content.php', ['id' => $id]));
$PAGE->set_title(get_string('editcontent', 'mod_valuemapdoc'));
$PAGE->set_heading(get_string('editcontent', 'mod_valuemapdoc'));

require_login($cm->course, true, $cm);
require_capability('mod/valuemapdoc:generatedocument', $context);


$groupmode = groups_get_activity_groupmode($cm);
$groupid = groups_get_activity_group($cm);
if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
    $usergroups = groups_get_user_groups($cm->course, $USER->id);
    if (!in_array($groupid, $usergroups[0])) {
        throw new moodle_exception('nopermission', 'mod_valuemapdoc');
        }
}

//$defaults = session_helper::get();
$document = $DB->get_record('valuemapdoc_content', ['id' => $docid], '*', MUST_EXIST);
$defaults['workingcontent'] = $document->content;

if (!empty($defaults['workingcontent']) && !is_array($defaults['workingcontent'])) {
    $defaults['workingcontent'] = [
        'text' => $defaults['workingcontent'],
        'format' => FORMAT_HTML,
    ];
}

$defaults['id'] = $id ;

// Tworzenie formularza
$mform = new \mod_valuemapdoc\form\edit_content_form(null,[
    'id' => $id,
    'docid' => $docid,
]);

$mform->set_data($defaults);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/valuemapdoc/rate_content.php', [
        'id' => $id,
        'docid' => $docid,
            ]));
} else if ($data = $mform->get_data()) {
    if (!empty($data->savecontent)) {
        // Kliknięto ZAPISZ
        $document->content = $data->workingcontent['text'];
        $DB->update_record('valuemapdoc_content', $document);
        redirect(new moodle_url('/mod/valuemapdoc/rate_content.php', [
            'id' => $id,
             'docid' => $docid,
            ]));
    } else if (!empty($data->tunecontent)) {
        // Kliknięto PRZEJDŹ DO TUNINGU
        $document->content = $data->workingcontent['text'];
        $DB->update_record('valuemapdoc_content', $document);

        redirect(new moodle_url('/mod/valuemapdoc/tune_content.php', ['id' => $id, 'docid' => $docid]));
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editcontent', 'mod_valuemapdoc'));
$mform->display();
echo $OUTPUT->footer();