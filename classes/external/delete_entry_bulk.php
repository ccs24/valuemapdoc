<?php
namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");


use external_function_parameters;
use external_api;
use external_value;
use external_multiple_structure;
use external_single_structure;
use external_util;
use core_user;
use context_module;

defined('MOODLE_INTERNAL') || die();

class delete_entry_bulk extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'entryids' => new external_multiple_structure(new external_value(PARAM_INT, 'Entry ID')),
            'cmid' => new external_value(PARAM_INT, 'Course module ID')
        ]);
    }

    public static function execute(array $entryids, int $cmid): bool {
        global $DB, $USER;

        [$course, $cm] = get_course_and_cm_from_cmid($cmid, 'valuemapdoc');
        $context = context_module::instance($cmid);
        self::validate_context($context);

        if (!has_capability('mod/valuemapdoc:view', $context)) {
            throw new required_capability_exception($context, 'mod/valuemapdoc:view', 'nopermissions', '');
        }


        $groupmode = groups_get_activity_groupmode($cm);
        $usergroups = groups_get_user_groups($cm->course, $USER->id);
        $valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);

        foreach ($entryids as $id) {
            $entry = $DB->get_record('valuemapdoc_entries', ['id' => $id], '*', IGNORE_MISSING);
            if (!$entry) {
                continue;
            }
            if (($entry->ismaster==1) && ($valuemapdoc->ismaster==0)) {
                continue; // Nie usuwaj rekordów masterowych
            }

            // Weryfikacja przynależności do grupy
            if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
                if (!in_array($entry->groupid, $usergroups[0])) {
                    continue;
                }
            }

            $DB->delete_records('valuemapdoc_entries', ['id' => $id]);
        }

        return true;
    }

    public static function execute_returns() {
        return new external_value(PARAM_BOOL, 'Returns true on success');
    }

}