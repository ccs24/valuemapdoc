<?php
namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_function_parameters;
use external_value;
use external_api;
use context_module;
use required_capability_exception;
use dml_exception;

class delete_content extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'entryid' => new external_value(PARAM_INT, 'ID rekordu do usunięcia'),
        ]);
    }

    public static function execute($entryid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['entryid' => $entryid]);

        $record = $DB->get_record('valuemapdoc_content', ['id' => $params['entryid']], '*', MUST_EXIST);

        $cm = get_coursemodule_from_id('valuemapdoc', $record->cmid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $valuemapdoc = $DB->get_record('valuemapdoc', array('id' => $cm->instance), '*', MUST_EXIST);

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        // Optional: Sprawdź, czy użytkownik ma uprawnienia (lub czy jest właścicielem).        
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
            $usergroups = groups_get_user_groups($cm->course, $USER->id);
            if (!in_array($record->groupid, $usergroups[0])) {
                throw new required_capability_exception($context, 'mod/valuemapdoc:edit', 'nopermissions', '');
            }
        }

        if ($record->userid != $USER->id && !has_capability('mod/valuemapdoc:edit', $context)) {
            throw new required_capability_exception($context, 'mod/valuemapdoc:edit', 'nopermissions', '');
        }

        $DB->delete_records('valuemapdoc_content', ['id' => $record->id]);

        return ['status' => 'ok'];
    }

    public static function execute_returns() {
        return new \external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Result: ok or error'),
        ]);
    }
}