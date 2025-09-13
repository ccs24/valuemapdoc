<?php

namespace mod_valuemapdoc\external;

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

use context_system;
use context_course;
use mod_valuemapdoc\valuemapdoc;
use moodle_exception;
use required_capability_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

class get_master_instances extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED)
        ]);
    }

    public static function execute($courseid) {
        global $DB, $CFG;;

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);

        $context = context_course::instance($courseid);
        self::validate_context($context);

        require_capability('mod/valuemapdoc:view', $context);
        require_once($CFG->dirroot . '/course/lib.php');

        $instances = $DB->get_records_sql("
            SELECT cm.id as cmid, v.name
            FROM {course_modules} cm
            JOIN {modules} m ON cm.module = m.id
            JOIN {valuemapdoc} v ON cm.instance = v.id
            WHERE cm.course = :courseid AND v.ismaster = 1 AND m.name = 'valuemapdoc'
        ", ['courseid' => $courseid]);

        global $USER;
        $usergroupsall = groups_get_user_groups($courseid, $USER->id);
        $userhasgroups = !empty($usergroupsall[0]);

        $result = [];
        foreach ($instances as $instance) {
            $cm = get_coursemodule_from_id('valuemapdoc', $instance->cmid, 0, false, MUST_EXIST);
            $contextmodule = \context_module::instance($cm->id);
            $groupmode = groups_get_activity_groupmode($cm);

            if ($groupmode == SEPARATEGROUPS && !$userhasgroups && !has_capability('moodle/site:accessallgroups', $contextmodule)) {
                continue; // użytkownik nie należy do żadnej grupy, a aktywność wymaga przynależności
            }

            $result[] = [
                'cmid' => $instance->cmid,
                'name' => $instance->name
            ];
        }

        return $result;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'name' => new external_value(PARAM_TEXT, 'Name of the instance')
            ])
        );
    }
}