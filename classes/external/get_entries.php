<?php
namespace mod_valuemapdoc\external;

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use context_course;
use context_module;
use required_capability_exception;

defined('MOODLE_INTERNAL') || die();

class get_entries extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'include_master' => new external_value(PARAM_INT, 'Include master entries'),
            'groupid' => new external_value(PARAM_INT, 'Only this group ID'),
        ]);
    }

    public static function execute($courseid=0, $cmid=0, $include_master = 0, $groupid = 0) {
        global $DB, $USER;


        // DEBUG - sprawdź co otrzymujemy
        error_log('GET_ENTRIES DEBUG: Raw params - courseid=' . var_export($courseid, true) . 
              ', cmid=' . var_export($cmid, true) . 
              ', include_master=' . var_export($include_master, true) . 
              ', groupid=' . var_export($groupid, true));


        // POPRAWKA: Musi być validate_parameters!
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'cmid' => $cmid,
            'include_master' => $include_master,
            'groupid' => $groupid,
        ]);
        
        // Użyj zwalidowanych parametrów
        $courseid = $params['courseid'];
        $cmid = $params['cmid'];
        $include_master = $params['include_master'];
        $groupid = $params['groupid'];

        $context = context_course::instance($courseid);
        self::validate_context($context);

        if (!has_capability('mod/valuemapdoc:view', $context)) {
            throw new required_capability_exception($context, 'mod/valuemapdoc:view', 'nopermissions', '');
        }

        $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);

        // Group mode handling.
        $groupmode = groups_get_activity_groupmode($cm);
        $gid = groups_get_activity_group($cm);
        $slavegroupid = $gid;
        $context = context_module::instance($cm->id);

        $hasallgroups = has_capability('moodle/site:accessallgroups', $context);
        
        $sql_params = [
            'cid' => $cm->id,
        ];

        // Podstawowa kwerenda
        $sql = "SELECT e.*, 
                       u.username, u.email, u.firstname, u.lastname,
                       CONCAT(u.firstname, ' ', u.lastname) as fullname
                FROM {valuemapdoc_entries} e
                JOIN {user} u ON u.id = e.userid
                WHERE e.cid = :cid";

        // Filtrowanie grup
        if ($groupmode == SEPARATEGROUPS && !$hasallgroups) {
            $usergroups = groups_get_user_groups($cm->course, $USER->id);
            if (!empty($usergroups[0])) {
                $grouplist = implode(',', $usergroups[0]);
                $sql .= " AND e.groupid IN ($grouplist)";
            } else {
                // User nie należy do żadnej grupy - nie widzi niczego
                $sql .= " AND e.groupid = -1";
            }
        }

        // Dodanie master entries jeśli wymagane
        if ($include_master > 0) {
            $mastercm = get_coursemodule_from_id('valuemapdoc', $include_master, 0, false, MUST_EXIST);
            $mastergroupmode = groups_get_activity_groupmode($mastercm);
            $mastergroupid = groups_get_activity_group($mastercm);
            
            $sql .= " UNION
                     SELECT e.*, 
                            u.username, u.email, u.firstname, u.lastname,
                            CONCAT(u.firstname, ' ', u.lastname) as fullname
                     FROM {valuemapdoc_entries} e
                     JOIN {user} u ON u.id = e.userid
                     WHERE e.cid = :mastercid AND e.ismaster = 1";
            
            $sql_params['mastercid'] = $mastercm->id;
            
            // Dodaj grupowanie dla master
            if ($mastergroupmode == SEPARATEGROUPS && !$hasallgroups) {
                if (!empty($usergroups[0])) {
                    $sql .= " AND e.groupid IN ($grouplist)";
                }
            }
        }

        $sql .= " ORDER BY e.timemodified DESC";

        $entries = $DB->get_records_sql($sql, $sql_params);

        $result = [];
        foreach ($entries as $entry) {
            $result[] = [
                'id' => (int)$entry->id,
                'market' => $entry->market ?? '',
                'industry' => $entry->industry ?? '',
                'role' => $entry->role ?? '',
                'businessgoal' => $entry->businessgoal ?? '',
                'strategy' => $entry->strategy ?? '',
                'difficulty' => $entry->difficulty ?? '',
                'situation' => $entry->situation ?? '',
                'statusquo' => $entry->statusquo ?? '',
                'coi' => $entry->coi ?? '',
                'differentiator' => $entry->differentiator ?? '',
                'impact' => $entry->impact ?? '',
                'newstate' => $entry->newstate ?? '',
                'successmetric' => $entry->successmetric ?? '',
                'impactstrategy' => $entry->impactstrategy ?? '',
                'impactbusinessgoal' => $entry->impactbusinessgoal ?? '',
                'impactothers' => $entry->impactothers ?? '',
                'proof' => $entry->proof ?? '',
                'time2results' => $entry->time2results ?? '',
                'quote' => $entry->quote ?? '',
                'clientname' => $entry->clientname ?? '',
                'ismaster' => (int)($entry->ismaster ?? 0),
                'groupid' => (int)($entry->groupid ?? 0),
                'username' => $entry->username ?? 'unknown',
                'email' => $entry->email ?? '',
                'fullname' => $entry->fullname ?? ($entry->username ?? 'Unknown User'),
            ];
        }
/*
        $result = [];
        $result[] = [
                'id' => 1,
                'market' => '',
                'industry' =>  '',
                'role' =>  '',
                'businessgoal' =>  '',
                'strategy' => '',
                'difficulty' =>  '',
                'situation' =>  '',
                'statusquo' =>  '',
                'coi' =>  '',
                'differentiator' =>  '',
                'impact' =>  '',
                'newstate' => '',
                'successmetric' =>  '',
                'impactstrategy' => '',
                'impactbusinessgoal' =>  '',
                'impactothers' =>  '',
                'proof' => '',
                'time2results' =>  '',
                'quote' =>  '',
                'clientname' =>  '',
                'ismaster' => (int)(0),
                'groupid' => (int)( 0),
                'username' =>  'unknown',
                'email' => '',
                'fullname' =>  'Unknown User',
        ];

*/
        return $result;
    }



    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID wpisu'),
                'market' => new external_value(PARAM_RAW, 'Market'),
                'industry' => new external_value(PARAM_RAW, 'Industry'),
                'role' => new external_value(PARAM_RAW, 'Role'),
                'businessgoal' => new external_value(PARAM_RAW, 'Business Goal'),
                'strategy' => new external_value(PARAM_RAW, 'Strategy'),
                'difficulty' => new external_value(PARAM_RAW, 'Difficulty'),
                'situation' => new external_value(PARAM_RAW, 'Situation'),
                'statusquo' => new external_value(PARAM_RAW, 'Status Quo'),
                'coi' => new external_value(PARAM_RAW, 'COI'),
                'differentiator' => new external_value(PARAM_RAW, 'Differentiator'),
                'impact' => new external_value(PARAM_RAW, 'Impact'),
                'newstate' => new external_value(PARAM_RAW, 'New State'),
                'successmetric' => new external_value(PARAM_RAW, 'Success Metric'),
                'impactstrategy' => new external_value(PARAM_RAW, 'Impact Strategy'),
                'impactbusinessgoal' => new external_value(PARAM_RAW, 'Impact Business Goal'),
                'impactothers' => new external_value(PARAM_RAW, 'Impact Others'),
                'proof' => new external_value(PARAM_RAW, 'Proof'),
                'time2results' => new external_value(PARAM_RAW, 'Time to Results'),
                'quote' => new external_value(PARAM_RAW, 'Quote'),
                'clientname' => new external_value(PARAM_RAW, 'Client Name'),
                'ismaster' => new external_value(PARAM_INT, 'Is master record'),
                'groupid' => new external_value(PARAM_INT, 'Group ID'),
                'username' => new external_value(PARAM_RAW, 'User Name'),
                'email' => new external_value(PARAM_RAW, 'User Email'),
                'fullname' => new external_value(PARAM_RAW, 'User Full Name'),
            ])
        );
    }
}