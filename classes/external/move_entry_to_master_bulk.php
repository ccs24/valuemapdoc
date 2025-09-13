<?php
namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");


use external_function_parameters;
use external_api;
use external_value;
use external_multiple_structure;
use context_module;

defined('MOODLE_INTERNAL') || die();

class move_entry_to_master_bulk extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'entryids' => new external_multiple_structure(new external_value(PARAM_INT, 'Entry ID')),
            'targetcmid' => new external_value(PARAM_INT, 'Target Master CMID')
        ]);
    }

    public static function execute(array $entryids, int $targetcmid): bool {
        global $DB, $USER;

        [$course, $cm] = get_course_and_cm_from_cmid($targetcmid, 'valuemapdoc');
//        $cm = get_coursemodule_from_id('valuemapdoc', $targetcmid, 0, false, MUST_EXIST);
        $cid = $cm->id;

        $context = context_module::instance($targetcmid);
        self::validate_context($context);

        require_capability('mod/valuemapdoc:manageentries', $context);

        // Get group mode and group id for the target activity.
        $groupmode = groups_get_activity_groupmode($cm);
        $groupid = groups_get_activity_group($cm);

        foreach ($entryids as $id) {
            $entry = $DB->get_record('valuemapdoc_entries', ['id' => $id], '*', IGNORE_MISSING);
            if (!$entry) {
                continue;
            }

            if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
                if ($entry->groupid != $groupid) {
                    continue;
                }
            }

            $entry->cid = $cid;
            $entry->ismaster = 1;
            $entry->groupid = $groupid;
            $DB->update_record('valuemapdoc_entries', $entry);
        }

        return true;
    }

    public static function execute_returns() {
        return new \external_value(PARAM_BOOL, 'True if success');
    }
}