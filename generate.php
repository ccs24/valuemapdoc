<?php
require_once('../../config.php');

use mod_valuemapdoc\local\markets;

$id = required_param('id', PARAM_INT); // course_module ID
$templateid = required_param('templateid', PARAM_INT); // template ID
if (empty($templateid)) {
    throw new \moodle_exception('missingtemplate', 'mod_valuemapdoc');
}

$marketid  = optional_param('marketid', 0, PARAM_INT);
$customerid  = optional_param('customerid', 0, PARAM_INT);
$personid  = optional_param('personid', 0, PARAM_INT);
$opportunityid  = optional_param('opportunityid', 0, PARAM_INT);

$filenameprefix = optional_param('filenameprefix', '', PARAM_TEXT);


$entryids_string = optional_param('entryids', "", PARAM_TEXT);

$selectedentries = [];
    if (!empty($entryids_string)) {
        $selectedentries = array_filter(
        array_map('intval', explode(',', $entryids_string)),
        function($id) { return $id > 0; }  // Usuń nieprawidłowe ID
        );
    }

/*
$selectedentries = optional_param_array('entryids', null, PARAM_INT);
if ($selectedentries === null) {
    $selectedentries = optional_param_array('entries', null, PARAM_INT);
}
*/

if (empty($selectedentries)) {
    echo $entryids_string;
    throw new moodle_exception('noentries', 'valuemapdoc');
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
$keyentries = array_keys($entries);

$name = markets::get_filename($marketid, $customerid, $personid, $opportunityid, $filenameprefix);
//var_dump($name , $marketid, $customerid, $personid, $opportunityid, $filenameprefix);die();

$content = new stdClass();
$content->courseid = $course->id;
$content->cmid = $cm->id;
$content->userid = $USER->id;
$content->name = $name ;
$content->customprompt = '';
$content->templateid = $templateid;
$content->marketid  = $marketid;
$content->customerid  = $customerid;
$content->personid  = $personid;
$content->opportunityid = $opportunityid;
$content->content = '';
//$content->entries = json_encode($keyentries);
$content->effectiveness = 0;
$content->feedback = '';
$content->timecreated = time();
$content->status = 'pending';
$content->groupid = $groupid;
$content->visibility = 0;

$contentid = $DB->insert_record('valuemapdoc_content', $content);


$task = new \mod_valuemapdoc\task\generate_document_task();
$task->set_custom_data([
    'courseid' => $course->id,
    'cmid' => $id,
    'groupid' => $groupid,
    'userid' => $USER->id,
    'entryids' => $keyentries , //array_keys($entries),
    'templateid' => $templateid,
    'marketid' => $marketid ?? 0,
    'customerid' => $customerid ?? 0,
    'personid' => $personid  ?? 0,
    'opportunityid' => $opportunityid ?? 0,
    'contentid' => $contentid
]);
\core\task\manager::queue_adhoc_task($task);

redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]),
        get_string('documentgenerated', 'mod_valuemapdoc'), 2);
