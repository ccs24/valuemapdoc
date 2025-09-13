<?php
namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/local/markets.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use mod_valuemapdoc\local\markets;

class get_content_entries extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'include_master' => new external_value(PARAM_INT, 'template ID', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute($courseid, $cmid, $include_master) {
        global $DB, $USER;

        self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'cmid' => $cmid,
            'include_master' => $include_master
        ]);

        $context = \context_module::instance($cmid);
        self::validate_context($context);

        $params = ['cmid' => $cmid];
        $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);
        $groupmode = groups_get_activity_groupmode($cm);
        $groupid = groups_get_activity_group($cm);

        $usergroups = groups_get_user_groups($cm->course, $USER->id);
        if ($groupmode == SEPARATEGROUPS && empty($usergroups[0]) && !has_capability('moodle/site:accessallgroups', $context)) {
            return []; // użytkownik bez grupy nie widzi nic
        }

        $sql = "
            SELECT c.id, c.userid, u.firstname, u.lastname,
                   c.templateid, t.templatetype, t.name AS templatename,
                   c.name,
                   c.customprompt, c.marketid, c.customerid, c.personid, c.opportunityid, 
                   c.timecreated, c.content, c.effectiveness,
                   c.visibility, c.status
            FROM {valuemapdoc_content} c
            JOIN {user} u ON u.id = c.userid
            LEFT JOIN {valuemapdoc_templates} t ON t.id = c.templateid
            WHERE c.cmid = :cmid
        ";

        // Filter by group if needed.
        if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
            $sql .= " AND (c.visibility = 0 OR c.userid = :userid) AND (c.groupid = :groupid)";
            $params['groupid'] = $groupid;
            $params['userid'] = $USER->id;
        }

        if ($groupmode != SEPARATEGROUPS || has_capability('moodle/site:accessallgroups', $context)) {
            $sql .= " AND (c.visibility = 0 OR c.userid = :userid)";
            $params['userid'] = $USER->id;
        }

        if ($include_master) {
            $sql .= " AND c.templateid = :templateid";
            $params['templateid'] = $include_master;
        }

        // Dodaj sortowanie - najnowsze na górze
        $sql .= " ORDER BY c.timecreated DESC";


        $records = $DB->get_records_sql($sql, $params);

        $results = [];
        foreach ($records as $rec) {
            $results[] = [
                'id' => $rec->id,
                'userid' => $rec->userid,
                'username' => $rec->firstname . ' ' . $rec->lastname,
                'name' => $rec->name ?? '(brak)',
                'templateid' => $rec->templateid ?? 0,
                'templatetype' => $rec->templatetype ?? '(brak)',
                'templatename' => $rec->templatename ?? '(brak)',
                'customprompt' => '',//$rec->customprompt ? shorten_text(strip_tags($rec->customprompt), 200) : '',
                'market' => $rec->marketid ? markets::get_by_id($rec->marketid)->name : '(brak)',
                'customer' => $rec->customerid ? markets::get_by_id($rec->customerid)->name : '(brak)',
                'person' => $rec->personid  ? markets::get_by_id($rec->personid)->name : '(brak)',
                'opportunity' => $rec->opportunityid ? markets::get_by_id($rec->opportunityid)->name : '(brak)',
                'timecreated' => userdate($rec->timecreated),
                'effectiveness' => $rec->effectiveness ?? 0,
                'content' => $rec->content ? shorten_text(strip_tags($rec->content), 200) : '',
                'status' => $rec->status ?? 'ready',
                'visibility' => $rec->visibility ?? 0,
            ];
        }

        return $results;
    }

    
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'User full name'),
                'name' => new external_value(PARAM_TEXT, 'Content name'),
                'templateid' => new external_value(PARAM_INT, 'Template ID'),
                'templatetype' => new external_value(PARAM_TEXT, 'Template type'),
                'templatename' => new external_value(PARAM_TEXT, 'Template name'),
                'customprompt' => new external_value(PARAM_TEXT, 'Custom Prompt'),
                'market' => new external_value(PARAM_TEXT, 'Market name'),
                'customer' => new external_value(PARAM_TEXT, 'Customer name'),
                'person' => new external_value(PARAM_TEXT, 'Person name'),      
                'opportunity' => new external_value(PARAM_TEXT, 'Opportunity Name'),
                'timecreated' => new external_value(PARAM_TEXT, 'Formatted creation time'),
                'effectiveness' => new external_value(PARAM_INT, 'Effectiveness score'),
                'content' => new external_value(PARAM_TEXT, 'Preview of content'),
                'status' => new external_value(PARAM_TEXT, 'Document generation status (pending, ready, error)'),
                'visibility' => new external_value(PARAM_INT, 'Visibility (0 = public, 1 = private)'),
            ])
        );
    }
}