<?php
require_once('../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);
require_login($courseid);
//require_capability('mod/valuemapdoc:manageentries', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/valuemapdoc/import_export.php', ['courseid' => $courseid]));
$PAGE->set_title('import export');
$PAGE->set_heading('import export');

echo $OUTPUT->header();

$action = optional_param('action', '', PARAM_ALPHA);

if ($action === 'import' && confirm_sesskey()) {
    $json = optional_param('jsondata', '', PARAM_RAW);
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo $OUTPUT->notification('❌ Invalid JSON: ' . json_last_error_msg(), 'notifyproblem');
    } else {
        global $DB, $USER;
        $imported = 0;
        foreach ($data as $record) {
            $entry = (object)[
                'course' => $courseid,
                'userid' => $USER->id,
                'timemodified' => time(),
                'market' => $record['market'] ?? '',
                'industry' => $record['industry'] ?? '',
                'role' => $record['role'] ?? '',
                'businessgoal' => $record['businessgoal'] ?? '',
                'strategy' => $record['strategy'] ?? '',
                'difficulty' => $record['difficulty'] ?? '',
                'situation' => $record['situation'] ?? '',
                'statusquo' => $record['statusquo'] ?? '',
                'coi' => $record['coi'] ?? '',
                'differentiator' => $record['differentiator'] ?? '',
                'impact' => $record['impact'] ?? '',
                'newstate' => $record['newstate'] ?? '',
                'successmetric' => $record['successmetric'] ?? '',
                'impactstrategy' => $record['impactstrategy'] ?? '',
                'impactbusinessgoal' => $record['impactbusinessgoal'] ?? '',
                'impactothers' => $record['impactothers'] ?? '',
                'proof' => $record['proof'] ?? '',
                'time2results' => $record['time2results'] ?? '',
                'quote' => $record['quote'] ?? '',
                'clientname' => $record['clientname'] ?? '',
            ];

            $DB->insert_record('valuemapdoc_entries', $entry);
            $imported++;
        }
        echo $OUTPUT->notification("✅ Imported $imported entries.", 'notifysuccess');
    }
}

// Prepare export.
$entries = $DB->get_records('valuemapdoc_entries', ['course' => $courseid]);
$json = json_encode(array_values($entries), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>

<h3>export valuemap</h3>

<textarea rows="20" cols="100" readonly><?= s($json) ?></textarea>

<h3>import valuemap </h3>
<form method="post">
    <input type="hidden" name="sesskey" value="<?= sesskey() ?>">
    <input type="hidden" name="courseid" value="<?= $courseid ?>">
    <input type="hidden" name="action" value="import">
    <textarea name="jsondata" rows="20" cols="100"></textarea><br><br>
    <input type="submit" value="import" class="btn btn-primary">
</form>

<?php
echo $OUTPUT->footer();