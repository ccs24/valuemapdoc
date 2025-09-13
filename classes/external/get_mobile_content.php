<?php
namespace mod_valuemapdoc\external;

require_once("$CFG->libdir/externallib.php");

use external_function_parameters;
use external_single_structure;
use external_value;
use external_api;

class get_mobile_content extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID')
        ]);
    }

    public static function execute($cmid) {
        global $DB;

        $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);
        $valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);

        return [
            'intro' => format_module_intro('valuemapdoc', $valuemapdoc, $cm->id),
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'intro' => new external_value(PARAM_RAW, 'Module intro formatted'),
        ]);
    }
}