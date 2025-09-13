<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT); // Course module ID
$opportunity = required_param('opportunity', PARAM_TEXT); // Opportunity name

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/valuemapdoc/opportunity.php', ['id' => $id, 'opportunity' => $opportunity]);
$PAGE->set_title(get_string('pluginname', 'mod_valuemapdoc') . ": " . format_string($opportunity));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'mod_valuemapdoc') . ": " . format_string($opportunity));

// Pobierz dokumenty powiązane z tą szansą
$docs = $DB->get_records('valuemapdoc_documents', [
    'course' => $course->id,
    'opportunity' => $opportunity
]);

if ($docs) {
    echo html_writer::start_tag('table', ['class' => 'generaltable']);
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('filename', 'mod_valuemapdoc'));
    echo html_writer::tag('th', get_string('user'));
    echo html_writer::tag('th', get_string('template', 'mod_valuemapdoc'));
    echo html_writer::tag('th', get_string('date'));
    echo html_writer::tag('th', get_string('result', 'mod_valuemapdoc'));
    echo html_writer::tag('th', get_string('action', 'mod_valuemapdoc'));
    echo html_writer::end_tag('tr');

    foreach ($docs as $doc) {
        $user = $DB->get_record('user', ['id' => $doc->userid], 'id, firstname, lastname');
        $template = $DB->get_record('valuemapdoc_templates', ['id' => $doc->templateid], 'name');

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', s($doc->filepath));
        echo html_writer::tag('td', fullname($user));
        echo html_writer::tag('td', s($template->name));
        echo html_writer::tag('td', userdate($doc->timecreated));
        echo html_writer::tag('td', s($doc->result ?? '-'));

        $rateurl = new moodle_url('/mod/valuemapdoc/rate_content.php', [
            'id' => $id,
            'docid' => $doc->id
        ]);
        $ratelink = html_writer::link($rateurl, get_string('ratedocument', 'mod_valuemapdoc'));

        echo html_writer::tag('td', $ratelink);
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('table');
} else {
    echo $OUTPUT->notification(get_string('nodocuments', 'mod_valuemapdoc'), 'notifymessage');
}

echo $OUTPUT->footer();
