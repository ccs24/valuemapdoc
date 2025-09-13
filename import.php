<?php
require('../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/classes/form/import_form.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$moduleinstance = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
$ismaster = $moduleinstance->ismaster;

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
require_login($course, false, $cm);
$context = context_module::instance($cm->id);

$groupmode = groups_get_activity_groupmode($cm);
$groupid = groups_get_activity_group($cm);

require_capability('mod/valuemapdoc:manageentries', $context);

// Formularz do uploadu
$mform = new mod_valuemapdoc_import_form(null, ['id' => $id]);
$mform->set_data(['id' => $id]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]));
} else if ($data = $mform->get_data()) {

    $importid = csv_import_reader::get_new_iid('valuemapdoc');
    $csv = new csv_import_reader($importid, 'valuemapdoc');
    $content = $mform->get_file_content('csvfile');

    
    // Spróbuj automatycznie wykryć separator: średnik vs przecinek
    $lines = explode("\n", $content);
    $sample = isset($lines[0]) ? $lines[0] : '';
    $delimiter = (substr_count($sample, ';') > substr_count($sample, ',')) ? 'semicolon' : 'comma';

    if (!$csv->load_csv_content($content, 'utf-8', $delimiter)) {
        die($csv->get_error($csv->get_errorcode()));
    }
    $csv->init();
    $columns = $csv->get_columns();

    $validfields = $DB->get_columns('valuemapdoc_entries');
    $invalid = array_diff($columns, array_keys($validfields));

    if (!empty($invalid)) {
        $msg = 'Import przerwany. Poniższe kolumny z pliku CSV nie istnieją w tabeli bazy danych:<br><ul>';
        foreach ($invalid as $badcol) {
            $msg .= '<li>' . s($badcol) . '</li>';
        }
        $msg .= '</ul>';
        $msg .= '<br>Kolumny dostępne w bazie danych:<br><ul>';
        foreach (array_keys($validfields) as $field) {
            $msg .= '<li>' . s($field) . '</li>';
        }
        $msg .= '</ul>';
        redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]), $msg, null, \core\output\notification::NOTIFY_ERROR);
    }

    $r = 0;
    
    while ($row = $csv->next()) {
            $record = new stdClass();

            // Przypisanie wartości zerowych do pól rekordu
            $record->course = $cm->course; 
            $record->cid = $cm->id;
            $record->userid = $USER->id;

            $reccol = [
                "market", "industry" , "role", "businessgoal", "strategy", 
                "difficulty", "situation", "statusquo", "coi", "differentiator",
                "impact", "newstate", "successmetric", "impactstrategy",
                "impactbusinessgoal", "impactothers", "proof", "time2results",
                "quote", "clientname", "ismaster", "maturity"
            ];
            foreach ($reccol as $colname) {
                $record->$colname = '';
            }
            $record->ismaster = $ismaster;
            $record->maturity = 0;

            // Przypisanie wartości z CSV do obiektu rekordu
            foreach ($columns as $i => $colname) {
                if (array_key_exists($colname, $validfields)) {
                    $record->$colname = $row[$i];
                }
            }

            // Nadpisanie kluczowych pól niezależnie od CSV
            $record->courseid = $course->id;
            $record->cid = $cm->id;
            $record->userid = $USER->id;
            $record->timemodified = time();
            $record->groupid = $groupid;

            // Usunięcie potencjalnego pola 'id' z CSV
            if (isset($record->id)) {
                unset($record->id);
            }


            $r++;

            $DB->insert_record('valuemapdoc_entries', $record);
        }

        $csv->close();
    redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]), $r . " " . get_string('imported', 'mod_valuemapdoc'));
    
}

// Wyświetl formularz
$PAGE->set_url(new moodle_url('/mod/valuemapdoc/import.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('importfromcsv', 'mod_valuemapdoc'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('importfromcsv', 'mod_valuemapdoc'));
$mform->display();
echo $OUTPUT->footer();