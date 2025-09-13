<?php
namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

class delete_entry extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'entryid' => new external_value(PARAM_INT, 'ID of the entry to delete'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    public static function execute($entryid, $cmid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'entryid' => $entryid,
            'cmid' => $cmid,
        ]);

        $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);

        $entry = $DB->get_record('valuemapdoc_entries', ['id' => $entryid], '*', MUST_EXIST);
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
            $usergroups = groups_get_user_groups($cm->course, $USER->id);
            if (!in_array($entry->groupid, $usergroups[0])) {
                throw new \required_capability_exception($context, 'mod/valuemapdoc:manageentries', 'nopermissions', '');
            }
        }
        // Sprawdzenie, czy użytkownik ma prawo usunąć wpis
        if ($entry->userid != $USER->id && !has_capability('mod/valuemapdoc:manageentries', $context)) {
           // throw new \moodle_exception('nopermissions', 'error', '', 'delete this entry');
        }
        $DB->delete_records('valuemapdoc_entries', ['id' => $entryid]);

        return ['status' => 'ok', 'entryid' => $entryid];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the operation'),
            'entryid' => new external_value(PARAM_INT, 'ID of the deleted entry'),
        ]);
    }
}