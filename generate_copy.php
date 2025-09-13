<?php
require_once('../../config.php');
require_once('classes/form/generate_form.php');
require_once('classes/local/generator.php');
require_once('classes/local/openai_client.php');
require_once('classes/local/prompt_logger.php');
require_once('classes/local/session_helper.php');
require_once('classes/local/markets.php');

use mod_valuemapdoc\local\markets;
use mod_valuemapdoc\local\generator;
use mod_valuemapdoc\local\document_logger;
use mod_valuemapdoc\local\openai_client;
use mod_valuemapdoc\local\session_helper;
use mod_valuemapdoc\local\prompt_logger;


$id = required_param('id', PARAM_INT); // course_module ID
$templateid = required_param('templateid', PARAM_INT); // template ID

$marketid  = optional_param('marketid', 0, PARAM_INT);
$customerid  = optional_param('customerid', 0, PARAM_INT);
$personid  = optional_param('personid', 0, PARAM_INT);
$opportunityid  = optional_param('opportunityid', 0, PARAM_INT);


            $market = markets::export_for_ai($marketid);
            $customer = markets::export_for_ai($customerid);
            $person = markets::export_for_ai($personid);
            $opportunity = markets::export_for_ai($opportunityid);

            $template_text = "
    Rynek: {market.name || 'Nieznany rynek'}
    Opis: {market.description || 'Brak opisu'}
    Rozmiar rynku: {market.market_size || 'Dane niedostępne'}
    Klient: {customer.name || 'Nazwa klienta nie została podana'}
    Branża: {customer.industry || 'Branża nieznana'}
    Nieistniejące pole: {market.nonexistent || 'Wartość domyślna'}
    ";


    $result = markets::replace_placeholders($template_text, $market, $customer, $person, $opportunity);
    
    echo "Wynik:\n" . $result;
var_dump($market, $customer, $person, $opportunity);die();

$selectedentries = optional_param_array('entryids', null, PARAM_INT);
if ($selectedentries === null) {
    $selectedentries = optional_param_array('entries', [], PARAM_INT);
}

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$groupid = groups_get_activity_group($cm);


list($in_sql, $params) = $DB->get_in_or_equal(array_values($selectedentries));
$params[] = $course->id;
$entries = $DB->get_records_select('valuemapdoc_entries', "id $in_sql AND course = ?", $params);
if (!$entries) {
    throw new moodle_exception('noentries', 'valuemapdoc');
}
if (empty($templateid)) {
    throw new \moodle_exception('missingtemplate', 'mod_valuemapdoc');
}

//var_dump($id, $templateid,$entries);die();

$content = new stdClass();
$content->courseid = $course->id;
$content->cmid = $cm->id;
$content->userid = $USER->id;
$content->customprompt = '';
$content->templateid = $templateid;
$content->marketid  = $marketid;
$content->customerid  = $customerid;
$content->personid  = $personid;
$content->opportunityid = $opportunityid;
$content->content = '';
$content->effectiveness = 0;
$content->feedback = '';
$content->timecreated = time();
$content->status = 'pending';
$content->groupid = $groupid;
$content->visibility = 0;

$contentid = $DB->insert_record('valuemapdoc_content', $content);


$PAGE->set_url(new moodle_url('/mod/valuemapdoc/generate.php', ['id' => $id]));
$PAGE->set_title(get_string('generatedocument', 'valuemapdoc'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

$mform = new valuemapdoc_generate_form(null, [
    'courseid' => $course->id,
    'entryids' => $selectedentries,
    'cmid' => $cm->id
]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]));
} else if ($data = $mform->get_data()) {

    // Sprawdź, czy użytkownik ma dostęp do wybranych wpisów
    if (!isset($data->entryids) || empty($data->entryids)) {
        // Powrót do formularza, nie rzucamy wyjątku, tylko pokazujemy formularz
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('generatedocument', 'mod_valuemapdoc'));
        echo $OUTPUT->notification(get_string('noentriesselected', 'mod_valuemapdoc'), 'notifyproblem');
        $mform->display();
        echo $OUTPUT->footer();
        exit;
    }

    list($in_sql, $params) = $DB->get_in_or_equal($data->entryids);
//    $params[] = $USER->id;
    $params[] = $course->id;
//    $entries = $DB->get_records_select('valuemapdoc_entries', "id $in_sql AND userid = ? AND course = ?", $params);
    $entries = $DB->get_records_select('valuemapdoc_entries', "id $in_sql AND course = ?", $params);
    if (!$entries) {
        throw new moodle_exception('noentries', 'valuemapdoc');
    }
    if (empty($data->templateid)) {
        throw new \moodle_exception('missingtemplate', 'mod_valuemapdoc');
    }
    $template = generator::load_template_by_id($data->templateid);
    $templateprompt = (is_object($template) && isset($template->prompt)) ? $template->prompt : '';
    $templatebody  = (is_object($template) && isset($template->templatebody)) ? $template->templatebody : '';

    if (empty($templatebody)) {
        throw new moodle_exception('notemplates', 'valuemapdoc');
        //halt?
    }

    $documentcontent = generator::generate_document(array_values($entries), $templatebody);

    $sourceknowledge =  generator::format_entries_for_prompt(array_values($entries));

    // Zbuduj prompt ChatGPT
    $systemprompt = get_config('mod_valuemapdoc', 'default_system_prompt');
    $templateintro = get_config('mod_valuemapdoc', 'default_template_prompt');
    $moduleinstance = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
    $activity_prompt = '';
    if (!empty($moduleinstance->activity_prompt)) {
        $activity_prompt  = $moduleinstance->activity_prompt;
    }

    $customprompt = isset($data->customprompt) ? $data->customprompt : '';

    $fullprompt = ""
//    . "\n" . $systemprompt
    . "\n" . $activity_prompt 
    . "\n" . $templateintro 
    . "\n" . $templateprompt 
    . "\n" . "Szablon:\n" . $templatebody
    . "\n" . $sourceknowledge 
    . "\n" . $customprompt;

    try {
    $response = openai_client::generate_text2($fullprompt, $systemprompt );
} catch (\Exception $e) {
    $promptid = \mod_valuemapdoc\local\prompt_logger::log_prompt_response(
        $USER->id, //userid
        $COURSE->id, //courseid
        $data->templateid ?? 0, //templateid
        $data->opportunityname ?? '', //opportunity
        $systemprompt . "|" . $fullprompt, //prompt
        'ERROR: ' . $e->getMessage(), //response
        null, //parentid
    );

   
    throw new \moodle_exception('openai_api_error', 'mod_valuemapdoc', '', null, $e->getMessage());
}

if (empty($response)) {
    $promptid = \mod_valuemapdoc\local\prompt_logger::log_prompt_response(
        $USER->id, //userid
        $COURSE->id, //courseid
        $data->templateid ?? 0, //templateid
        $data->opportunityname ?? '', //opportunity
        $systemprompt . "|" . $fullprompt, //prompt
        'ERROR: ' . 'no_response_from_openai', //response
       null, //parentid
    );

    throw new \moodle_exception('no_response_from_openai', 'mod_valuemapdoc');
}


$promptid = \mod_valuemapdoc\local\prompt_logger::log_prompt_response(
    $USER->id, //userid
    $COURSE->id, //courseid
    $data->templateid ?? 0, //templateid
    $data->opportunityname ?? '', //opportunity
    $systemprompt . "|" . $fullprompt, //prompt
    $response, //response
    \mod_valuemapdoc\local\session_helper::get('promptid') //parentid
);

// Update current prompt id in session.
\mod_valuemapdoc\local\session_helper::set('promptid', $promptid);



    if ($response) {
        $documentcontent = $response;
    }

    $doc = [
        'text' => $documentcontent,
        'format' => FORMAT_HTML,
    ];
    session_helper::save([
        'workingcontent' => $doc,
        'templateid' => $data->templateid,
        'opportunityname' => $data->opportunityname,
        'promptid' => $promptid,
        'userid' => $USER->id,
        'customprompt' => $systemprompt . "|" . $fullprompt, //$data->customprompt,      
        'entryids' => $data->entryids,
    ]);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('previewdocument', 'mod_valuemapdoc'));
    // Bootstrap container
    echo html_writer::start_div('container-fluid');
    echo html_writer::start_div('row');

    // LEWA KOLUMNA – PRZYCISKI
    echo html_writer::start_div('col-md-3 d-flex flex-column gap-3');
    // Przycisk: zacznij od nowa
    echo html_writer::link(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]), get_string('startover', 'mod_valuemapdoc'), ['class' => 'btn btn-secondary btn-block']);

    // Formularz: edytuj
    echo html_writer::start_tag('form', ['method' => 'post', 'action' => new moodle_url('/mod/valuemapdoc/edit_content.php')]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $cm->course]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'templateid', 'value' => $data->templateid]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'customprompt', 'value' => $data->customprompt]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'opportunityname', 'value' => $data->opportunityname]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'documentcontent', 'value' => $documentcontent]);
    echo html_writer::tag('button', get_string('edit', 'mod_valuemapdoc'), ['type' => 'submit', 'class' => 'btn btn-warning btn-block']);
    echo html_writer::end_tag('form');

/*    echo html_writer::hidden_fields([
     'id' => $id,
     'templateid' => $data->templateid,
     'customprompt' => $data->customprompt,
     'opportunityname' => $data->opportunityname,
     'documentcontent' => $documentcontent,
     'startoverurl' => $startoverurl,
     'backtogenerationurl' => $backtogenerationurl
    ]);
    
    echo html_writer::tag('button', get_string('edit', 'mod_valuemapdoc'), ['type' => 'submit', 'class' => 'btn btn-warning btn-block']);
    echo html_writer::end_tag('form');
    */

    // Formularz: tuning
    echo html_writer::start_tag('form', ['method' => 'post', 'action' => new moodle_url('/mod/valuemapdoc/tune_content.php')]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $cm->course]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'templateid', 'value' => $data->templateid]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'customprompt', 'value' => $data->customprompt]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'opportunityname', 'value' => $data->opportunityname]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'documentcontent', 'value' => $documentcontent]);
    echo html_writer::tag('button', get_string('tuning', 'mod_valuemapdoc'), ['type' => 'submit', 'class' => 'btn btn-info btn-block']);
    echo html_writer::end_tag('form');

/*    echo html_writer::hidden_fields([
     'id' => $id,
     'templateid' => $data->templateid,
     'customprompt' => $data->customprompt,
     'opportunityname' => $data->opportunityname,
     'documentcontent' => $documentcontent,
     'prompt' => $fullprompt,
     'entries' => json_encode($data->entryids),
     'startoverurl' => $startoverurl,
     'backtogenerationurl' => $backtogenerationurl
    ]);
    echo html_writer::tag('button', get_string('tuning', 'mod_valuemapdoc'), ['type' => 'submit', 'class' => 'btn btn-info btn-block']);
    echo html_writer::end_tag('form');*/


    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => new moodle_url('/mod/valuemapdoc/save_content.php')
    ]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'documentcontent', 'value' => /*base64_encode*/($documentcontent)]); //; format_text($documentcontent, FORMAT_HTML)
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $course->id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userid', 'value' => $USER->id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'templateid', 'value' => $data->templateid]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'customprompt', 'value' => $data->customprompt]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'opportunityname', 'value' => $data->opportunityname]);
    echo html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-primary btn-block', 'value' => get_string('savechanges')]);
    echo html_writer::end_tag('form');
    





    echo html_writer::end_div(); // .col-md-3

    // PRAWA KOLUMNA – PODGLĄD
    echo html_writer::start_div('col-md-9');
    echo html_writer::start_div('border rounded p-4 bg-light', ['style' => 'max-height: 80vh; overflow-y: auto;']);
    echo format_text($documentcontent, FORMAT_HTML);
    echo html_writer::end_div();
    echo html_writer::end_div(); // .col-md-9

    echo html_writer::end_div(); // .row
    echo html_writer::end_div(); // .container-fluid

    echo $OUTPUT->footer();



/*
    echo html_writer::div(format_text($documentcontent, FORMAT_HTML), 'valuemapdoc-preview readonly-preview');

    $startoverurl = new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]);
    $backtogenerationurl = new moodle_url('/mod/valuemapdoc/generate.php', ['id' => $id]);
    // Removed edit url; using POST form below instead
    $tuningurl = new moodle_url('/mod/valuemapdoc/tune_content.php', ['prompt' => $fullprompt, 'entries' => json_encode($data->entryids), 'output' => $documentcontent]);

    echo html_writer::tag('a', get_string('startover', 'mod_valuemapdoc'), ['href' => $startoverurl, 'class' => 'btn btn-secondary']);
    echo html_writer::tag('a', get_string('backtogeneration', 'mod_valuemapdoc'), ['href' => $backtogenerationurl, 'class' => 'btn btn-secondary']);
    echo html_writer::start_tag('form', ['method' => 'post', 'action' => new moodle_url('/mod/valuemapdoc/edit_content.php')]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'templateid', 'value' => $data->templateid]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'customprompt', 'value' => $data->customprompt]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'opportunityname', 'value' => $data->opportunityname]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'documentcontent', 'value' => $documentcontent]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'startoverurl', 'value' => $startoverurl]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'backtogenerationurl', 'value' => $backtogenerationurl]);
    echo html_writer::tag('button', get_string('edit', 'mod_valuemapdoc'), ['type' => 'submit', 'class' => 'btn btn-warning']);
    echo html_writer::end_tag('form');
    echo html_writer::start_tag('form', ['method' => 'post', 'action' => new moodle_url('/mod/valuemapdoc/tune_content.php')]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'templateid', 'value' => $data->templateid]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'customprompt', 'value' => $data->customprompt]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'opportunityname', 'value' => $data->opportunityname]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'documentcontent', 'value' => $documentcontent]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'prompt', 'value' => $fullprompt]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'entries', 'value' => json_encode($data->entryids)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'startoverurl', 'value' => $startoverurl]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'backtogenerationurl', 'value' => $backtogenerationurl]);
    echo html_writer::tag('button', get_string('tuning', 'mod_valuemapdoc'), ['type' => 'submit', 'class' => 'btn btn-info']);
    echo html_writer::end_tag('form');

    echo $OUTPUT->footer();*/
    exit;

} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('generatedocument', 'valuemapdoc'));
    $mform->display();
    echo $OUTPUT->footer();
}
