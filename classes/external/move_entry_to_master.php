<?php
namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_module;
use stdClass;

class move_entry_to_master extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'entryid' => new external_value(PARAM_INT, 'ID of the entry to move'),
            'targetcmid' => new external_value(PARAM_INT, 'Course module ID of the master activity')
        ]);
    }

    public static function execute($entryid, $targetcmid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'entryid' => $entryid,
            'targetcmid' => $targetcmid
        ]);

        $targetcm = get_coursemodule_from_id('valuemapdoc', $params['targetcmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($targetcm->id);
        self::validate_context($context);

        $groupmode = groups_get_activity_groupmode($targetcm);
        $groupid = groups_get_activity_group($targetcm);
        if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
            if ($entry->groupid != $groupid) {
                throw new \required_capability_exception($context, 'mod/valuemapdoc:manageentries', 'nopermissions', '');
            }
        }

//        require_capability('mod/valuemapdoc:edit', $context);
        require_capability('mod/valuemapdoc:manageentries', $context);

        $targetinstance = $DB->get_record('valuemapdoc', ['id' => $targetcm->instance], '*', MUST_EXIST);
        if (!$targetinstance->ismaster) {
            throw new \moodle_exception('Target activity is not marked as master');
        }

        $entry = $DB->get_record('valuemapdoc_entries', ['id' => $params['entryid']], '*', MUST_EXIST);

        // Prepare entry for move
        $entry->cid = $targetcm->instance;
        $entry->course = $targetcm->course;
        $entry->ismaster = 1;
        $entry->timemodified = time();

        $entry->groupid = $groupid;

        $DB->update_record('valuemapdoc_entries', $entry);

        return ['status' => 'success', 'entryid' => $entry->id];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the operation'),
            'entryid' => new external_value(PARAM_INT, 'ID of the moved entry')
        ]);
    }
}
