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

class create_entries_bulk extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT),
            'entryids' => new external_multiple_structure(new external_value(PARAM_INT, 'Entry ID')),
            ]);
    }

    public static function execute($cmid, $entryids) {
        global $DB, $USER;

        $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $groupmode = groups_get_activity_groupmode($cm);
        $gid = groups_get_activity_group($cm);
        $usergroups = groups_get_user_groups($cm->course, $USER->id);
        $valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);


//        require_capability('mod/valuemapdoc:edit', $context);

        $results = [];

        if (empty($entryids) || !is_array($entryids)) {
                $entry = [
                'timemodified' => time(),
                'cid' => $cmid,
                'course' => $cm->course,
                'userid' => $USER->id,
                'id' => 0,
                'market' => '',
                'industry' => '',
                'role' => '',
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
                'groupid' => $gid,
                'ismaster' => $valuemapdoc->ismaster,
            ];

            

            
            $entry['id'] = $DB->insert_record('valuemapdoc_entries', $entry);
            $results[] = (array)$entry;
            return  $results;
             
        }

        

        foreach ($entryids as $id) {
            $entry = $DB->get_record('valuemapdoc_entries', ['id' => $id], '*', IGNORE_MISSING);
            if (!$entry) {
                continue;
            }

            if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
                if (!in_array($entry->groupid, $usergroups[0])) {
                    continue;
                }
            }

            unset($entry->id);
            $entry->timemodified = time();
            $entry->cid = $cmid;
            $entry->userid = $USER->id;
            $entry->ismaster = $valuemapdoc->ismaster;
            $entry->groupid = $gid;


            $entry->id = $DB->insert_record('valuemapdoc_entries', $entry);
            $results[] = (array)$entry;
        }

        return $results;
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
                'ismaster' => new external_value(PARAM_INT, 'Is master record'),            ])
        );
    }
}