<?php
require_once('../../config.php');
require_once('classes/form/rate_content_form.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/local/markets.php');

//require_once('classes/local/storage.php');
use mod_valuemapdoc\local\markets;

use mod_valuemapdoc\local\storage;

use mod_valuemapdoc\form\rate_content_form;
use mod_valuemapdoc\local\content_mailer; 
use mod_valuemapdoc\local\content_saver;

$id = required_param('id', PARAM_INT); // Course module ID
$docid = required_param('docid', PARAM_INT); //doc ID

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$groupmode = groups_get_activity_groupmode($cm);
$groupid = groups_get_activity_group($cm);

$PAGE->set_context($context); // najpierw KONTEKST
$PAGE->set_url('/mod/valuemapdoc/rate.php', ['id' => $id, 'docid' => $docid]);
$PAGE->set_title(get_string('ratedocument', 'mod_valuemapdoc'));
$PAGE->set_heading(get_string('pluginname', 'mod_valuemapdoc'));

$PAGE->requires->js_call_amd('mod_valuemapdoc/ratecontent', 'init');


    $params = ['docid' => $docid];
        $sql = "
            SELECT c.id, c.userid, u.firstname, u.lastname,
                   c.templateid, t.templatetype, t.name AS templatename,
                   c.customprompt, c.marketid, c.customerid, c.personid, c.opportunityid, 
                   c.timecreated, c.content,
                   c.effectiveness, c.feedback, c.groupid
            FROM {valuemapdoc_content} c
            JOIN {user} u ON u.id = c.userid
            LEFT JOIN {valuemapdoc_templates} t ON t.id = c.templateid
            WHERE c.id = :docid
        ";

    $records = $DB->get_records_sql($sql, $params);

    if ($records) {
        foreach ($records as $rec) {
            if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
                $usergroups = groups_get_user_groups($course->id, $USER->id);
                if (!in_array($rec->groupid, $usergroups[0])) {
                    throw new moodle_exception('nopermission', 'mod_valuemapdoc');
                }
            }
            $user = core_user::get_user($rec->userid);
            $data = new stdClass();
            $data->docid = $rec->id;
            $data->id = $id;
            $data->userid = $rec->userid;
            $data->username = fullname($user);
            $data->templateid = $rec->templateid ?? 0;
            $data->templatetype = $rec->templatetype ?? '(brak)';
            $data->templatename = $rec->templatename ?? '(brak)';
            $data->customprompt = $rec->customprompt ?? '';
            $data->opportunityname = $rec->opportunityname ?? '(brak)';
            $data->timecreated = userdate($rec->timecreated);
            $data->effectiveness = $rec->effectiveness ?? 0;
            $data->feedback = $rec->feedback ?? '';
            $data->content = $rec->content;
        }
    }

$mform = new rate_content_form(null, [
    'docid' => $docid,
    'id' => $id,
    'documentcontent' => $data->content
]);

$mform->set_data($data);


if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id],'content-tab'));
} else if ($data = $mform->get_data()) {
        // Obsłuż różne typy akcji
    switch ($data->action_type) {
        case 'save_file':
            storage::save_content_as_localfile($docid);

            \core\notification::success('File saved!');
        
            
        case 'send_mail':
            // Wyślij e-mailem
            content_mailer::send_by_entryid($docid);
            
            break;
            
        case 'quick_save':
        case 'full_save':
        default:

            $DB->set_field('valuemapdoc_content', 'effectiveness', $data->effectiveness, ['id' => $docid]);
            $DB->set_field('valuemapdoc_content', 'feedback', $data->feedback, ['id' => $docid]);
            if (isset($data->visibility)) {
                $DB->set_field('valuemapdoc_content', 'visibility', $data->visibility, ['id' => $docid]);
            }
            \core\notification::success(get_string('settingssaved', 'mod_valuemapdoc'));
    }


    } // Wyświetl stronę



    




echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('ratedocument', 'mod_valuemapdoc'));
$mform->display();
echo $OUTPUT->footer();