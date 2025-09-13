<?php

namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");


use external_function_parameters;
use external_value;
use external_single_structure;
use external_api;
use context_module;
use moodle_exception;
use required_capability_exception;

class update_cell extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'ID rekordu'),
            'field' => new external_value(PARAM_ALPHANUMEXT, 'Pole'),
            'value' => new external_value(PARAM_RAW, 'Nowa wartość'),
        ]);
    }

    public static function execute($id, $field, $value): array {
        global $DB, $USER;

        self::validate_parameters(self::execute_parameters(), [
            'id' => $id,
            'field' => $field,
            'value' => $value,
        ]);

        // (sprawdzenie sesskey i uprawnień możesz dodać tu)
        // Dopuszczalne pola do edycji — ochrona przed SQL injection
        $allowedfields = [
            'market', 'industry', 'role', 'businessgoal', 'strategy', 'difficulty',
            'situation', 'statusquo', 'coi', 'differentiator', 'impact', 'newstate',
            'successmetric', 'impactstrategy', 'impactbusinessgoal', 'impactothers',
            'proof', 'time2results', 'quote', 'clientname'
        ];
        //$allowedfields = ['market', 'industry', 'role', 'businessgoal', 'pain', 'value', 'insight', 'action', 'source'];


        if (!in_array($field, $allowedfields)) {
            throw new moodle_exception('invalidfield', 'error', '', $field);
        }

        $entry = $DB->get_record('valuemapdoc_entries', ['id' => $id], '*', MUST_EXIST);
        $cm = get_coursemodule_from_id('valuemapdoc', $entry->cid, 0, MUST_EXIST);
        $context = context_module::instance($cm->id);

        // Weryfikacja uprawnień grupowych
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
            $usergroups = groups_get_user_groups($cm->course, $USER->id);
            if (!in_array($entry->groupid, $usergroups[0])) {
                throw new required_capability_exception($context, 'mod/valuemapdoc:editownentry', 'nopermissions', '');
            }
        }

        $DB->set_field('valuemapdoc_entries', $field, $value, ['id' => $id]);

        return [
            'status' => 'ok',
            'id' => $id,
            'field' => $field,
            'value' => $value
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT),
            'id' => new external_value(PARAM_INT),
            'field' => new external_value(PARAM_TEXT),
            'value' => new external_value(PARAM_RAW),
        ]);
    }
}