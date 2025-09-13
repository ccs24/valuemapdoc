<?php
require('../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');


$id = required_param('id', PARAM_INT); // Course module ID

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
require_login($course, false, $cm);

$context = context_module::instance($cm->id);
$groupmode = groups_get_activity_groupmode($cm);
$groupid = groups_get_activity_group($cm);

$selectedids = optional_param_array('entryids', [], PARAM_INT); // entryids[] jako lista

if (!empty($selectedids)) {
    $where = "id " . $DB->get_in_or_equal($selectedids)[0] . " AND cid = ?";
    $queryparams = array_merge($DB->get_in_or_equal($selectedids)[1], [$cm->id]);

    if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
        $where .= " AND groupid = ?";
        $queryparams[] = $groupid;
    }

    $entries = $DB->get_records_select('valuemapdoc_entries', $where, $queryparams);
} else {
    $params = ['cid' => $cm->id];
    $where = 'cid = :cid';

    if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
        $where .= ' AND groupid = :groupid';
        $params['groupid'] = $groupid;
    }

    $entries = $DB->get_records_select('valuemapdoc_entries', $where, $params);
}


$filename = clean_filename('valuemapdoc_export_' . date('Ymd_His')) . '.csv';
$exporter = new csv_export_writer();
$exporter->set_filename($filename);

// Kolumny
$headers = [
    'market', 'industry', 'role', 'businessgoal', 'strategy', 'difficulty', 'situation',
    'statusquo', 'coi', 'differentiator', 'impact', 'newstate', 'successmetric',
    'impactstrategy', 'impactbusinessgoal', 'impactothers', 'proof', 'time2results',
    'quote', 'clientname'
];

$exporter->add_data($headers);

// Dane
foreach ($entries as $entry) {
    $row = [];
    foreach ($headers as $field) {
        $row[] = $entry->$field;
    }
    $exporter->add_data($row);
}

$exporter->download_file(); // Zakończy skrypt