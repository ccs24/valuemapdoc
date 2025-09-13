<?php
namespace mod_valuemapdoc\external;

require_once("$CFG->libdir/externallib.php");

//require_once($CFG->dirroot.'/mod/valuemapdoc/classes/Logger.php');
//use mod_valuemapdoc\Logger;


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

    public static function execute($courseid, $cmid, $include_master = 0, $groupid = 0) {
        global $DB, $USER;

/*        return [
            'cmid' => $cmid,
            'courseid' => $courseid,
            'include_master' => $include_master
        ]; */
        /*
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'cmid' => $cmid,
            'include_master' => $include_master,
        ]);
        */
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
        
        $params = [
                'cid' => $cm->id,
                'userid' => $USER->id,
            ];



        if (($include_master > 0)) {
            //$mastercm = get_coursemodule_from_instance//
            $mastercm = get_coursemodule_from_id('valuemapdoc', $include_master, 0, false, MUST_EXIST);
            $mastergroupmode = groups_get_activity_groupmode($mastercm);
            $mastergroupid = groups_get_activity_group($mastercm);

            // Czy użytkownik ma prawo przeglądać grupy?
            $hasallgroups = has_capability('moodle/site:accessallgroups', $context);


            $params['include_master' ] = $mastercm->id;


            switch ($mastergroupmode) {
                case SEPARATEGROUPS: //MASTER oddzielna grupa
                    $mastergroups = groups_get_user_groups($mastercm->course, $USER->id);
                    if (empty($mastergroups[0])) {
                    // User does not belong to any group in Master, and Master uses separate groups.
                        return [];
                    }


                    switch ($groupmode) { // lokalnie oddzielne grupy zdalnie oddzielne grupy
                        case SEPARATEGROUPS:
                        if ($hasallgroups && $groupid > 0) {
                            // SAY — admin wybrał grupę
                            $params['groupid_slave'] = $groupid;
                            $params['groupid_master'] = $groupid;
                            $where = '((cid = :cid AND groupid = :groupid_slave) OR (cid = :include_master AND groupid = :groupid_master))';
                         } else if($hasallgroups && $groupid == 0) {
                            $where = '((cid = :cid) OR (cid = :include_master))';
                        } else{
                            // SBY — zwykły użytkownik lub admin bez grupy — tylko jego grupa
                            $params['groupid_slave'] = $gid;
                            $params['groupid_master'] = $gid;
                            $where = '((cid = :cid AND groupid = :groupid_slave) OR (cid = :include_master AND groupid = :groupid_master))';
                        }
                        break;
                        case VISIBLEGROUPS: //lokalnie visible groups zdalnie oddzielne grupy
                            if ($hasallgroups && $groupid > 0) {
                                $params['groupid_slave'] = $groupid;
                                $params['groupid_master'] = $groupid;
                                $where = '((cid = :cid AND groupid = :groupid_slave) OR (cid = :include_master AND groupid = :groupid_master))';
                           } else {
                                $params['groupid_master'] = $gid;
                                $where = '((cid = :cid) OR (cid = :include_master AND groupid = :groupid_master))';
                            }
                        break;
                        case NOGROUPS: //lokalnie nogroups zdalnie oddzielne grupy
                            // W NOGROUPS nic nie zmieniamy
                                $params['groupid_master'] = $gid;
                                $where = '((cid = :cid) OR (cid = :include_master AND groupid = :groupid_master))';
                        break;
                    } //switch

//               
                    break;
                case VISIBLEGROUPS: //Master widzialne grupy -> gdy 
                    switch ($groupmode) { // lokalnie oddzielne grupy zdalnie oddzielne grupy
                        case SEPARATEGROUPS:
                        if ($hasallgroups && $groupid > 0) {
                            // SAY — admin wybrał grupę
                            $params['groupid_slave'] = $groupid;
                            $params['groupid_master'] = $groupid;
                            $where = '((cid = :cid AND groupid = :groupid_slave) OR (cid = :include_master AND groupid = :groupid_master))';
                        } else {
                            // SBY — zwykły użytkownik lub admin bez grupy — tylko jego grupa
                            $params['groupid_slave'] = $gid;
                            $where = '((cid = :cid AND groupid = :groupid_slave) OR (cid = :include_master))';// AND groupid = :groupid_master))';
                        }
                        break;
                        case VISIBLEGROUPS: //lokalnie visible groups zdalnie oddzielne grupy
                            if ($hasallgroups && $groupid > 0) {
                                $params['groupid_slave'] = $groupid;
                                $params['groupid_master'] = $groupid;
                                $where = '((cid = :cid AND groupid = :groupid_slave) OR (cid = :include_master AND groupid = :groupid_master))';
                           } else {
                                $params['groupid_master'] = $gid;
                                $where = '((cid = :cid) OR (cid = :include_master AND groupid = :groupid_master))';
                            }
                        break;
                        case NOGROUPS: //lokalnie nogroups zdalnie oddzielne grupy
                            // W NOGROUPS nic nie zmieniamy
                                $params['groupid_master'] = $gid;
                                $where = '((cid = :cid) OR (cid = :include_master AND groupid = :groupid_master))';
                        break;
                    } //switch

                    break;
                case NOGROUPS:
                        // Master nie ma grup
                    switch ($groupmode) { // lokalnie oddzielne grupy zdalnie oddzielne grupy
                        case SEPARATEGROUPS:
                        if ($hasallgroups && $groupid > 0) {
                            // SAY — admin wybrał grupę
                            $params['groupid_slave'] = $groupid;
                            $params['groupid_master'] = $groupid;
                            $where = '((cid = :cid AND groupid = :groupid_slave) OR (cid = :include_master AND groupid = :groupid_master))';
                        } else {
                            // SBY — zwykły użytkownik lub admin bez grupy — tylko jego grupa
                            $params['groupid_slave'] = $gid;
                            //$params['groupid_master'] = $gid;
                            $where = '((cid = :cid AND groupid = :groupid_slave) OR (cid = :include_master))';// AND groupid = :groupid_master))';
                        }
                        break;
                        case VISIBLEGROUPS: //lokalnie visible groups zdalnie oddzielne grupy
                            if ($hasallgroups && $groupid > 0) {
                                $params['groupid_master'] = $groupid;
                                $where = '((cid = :cid) OR (cid = :include_master AND groupid = :groupid_master))';
                           } else {
                                $params['groupid_master'] = $gid;
                                $where = '((cid = :cid) OR (cid = :include_master AND groupid = :groupid_master))';
                            }
                        break;
                        case NOGROUPS: //lokalnie nogroups zdalnie oddzielne grupy
                            // W NOGROUPS nic nie zmieniamy
                                $where = '((cid = :cid) OR (cid = :include_master))';
                        break;
                    } //switch
                    
                    break;
            } //switch

        } else { //if (($include_master == 0)) - LOKALNE REKORDY

            switch ($groupmode) {
                case SEPARATEGROUPS:
                    if ($hasallgroups && $groupid > 0) {
                        // SAY — admin wybrał grupę
                        $params['groupid'] = $groupid;
                        $where = '(cid = :cid AND groupid = :groupid)';
                    } else if($hasallgroups && $groupid == 0) {
                        $where = '((cid = :cid))';
                    } else{
                        // SBY — zwykły użytkownik lub admin bez grupy — tylko jego grupa
                        $params['groupid'] = $gid;                       
                        $where = '(cid = :cid AND groupid = :groupid)';
                }
                    break;
                case VISIBLEGROUPS:
                    if ($hasallgroups && $groupid > 0) {
                        // VAY
                        $params['groupid'] = $groupid;
                        $where = '(cid = :cid AND groupid = :groupid)';
                    } else {
                        // VBY
                        $params['groupid'] = $gid;
                        $where = '(cid = :cid AND groupid = :groupid)';
                    }
                    break;
                case NOGROUPS:
                    // W NOGROUPS nic nie zmieniamy
                    $where = '((cid = :cid))'; // OR (cid = :include_master))';
                    break;
            }
        
        }
/*

        $result[] = [
                'id' => 1,
                'market' => json_encode($params),
                'industry' => $where,
                'role' => '' . $gid,
                'businessgoal' => '',
                'strategy' => '',
                'difficulty' => '',
                'situation' => '',
                'statusquo' => '',
                'coi' => '',
                'differentiator' => '',
                'impact' => '',
                'newstate' => '',
                'successmetric' => '',
                'impactstrategy' => '',
                'impactbusinessgoal' => '',
                'impactothers' => '',
                'proof' => '',
                'time2results' => '',
                'quote' => '',
                'clientname' => '',
                'ismaster' => 0,
                'groupid' => 2,
            ];

            return $result; 

        $entries = $DB->get_records_select('valuemapdoc_entries', $where, $params);
        $result = [];

*/

        $sql = "SELECT ve.*, u.username, u.firstname, u.lastname, u.email
        FROM {valuemapdoc_entries} ve 
        LEFT JOIN {user} u ON ve.userid = u.id 
        WHERE $where";

        $entries = $DB->get_records_sql($sql, $params);

        $result = [];

        foreach ($entries as $entry) {

/*            $userobj = (object)[
                'firstname' => $entry->firstname,
                'lastname' => $entry->lastname
            ];

            $displayname = fullname($userobj);
*/
            $result[] = [
                'id' => $entry->id,
                'market' => $entry->market,
                'industry' => $entry->industry,
                'role' => $entry->role,
                'businessgoal' => $entry->businessgoal,
                'strategy' => $entry->strategy,
                'difficulty' => $entry->difficulty,
                'situation' => $entry->situation,
                'statusquo' => $entry->statusquo,
                'coi' => $entry->coi,
                'differentiator' => $entry->differentiator,
                'impact' => $entry->impact,
                'newstate' => $entry->newstate,
                'successmetric' => $entry->successmetric,
                'impactstrategy' => $entry->impactstrategy,
                'impactbusinessgoal' => $entry->impactbusinessgoal,
                'impactothers' => $entry->impactothers,
                'proof' => $entry->proof,
                'time2results' => $entry->time2results,
                'quote' => $entry->quote,
                'clientname' => $entry->clientname,
                'ismaster' => $entry->ismaster,
                'groupid' => $entry->groupid,

                'username' => $entry->username ?? 'unknown',           // Rzeczywisty username
                'email' => $entry->email ?? 'email',                        // Email osobno
                'fullname' => $entry->username ?? 'fulname', //$displayname ?: ($entry->username ?? 'Unknown User'), // Sformatowane imię
            ];



        }



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