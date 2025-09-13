<?php

namespace mod_valuemapdoc\local;

defined('MOODLE_INTERNAL') || die();

use core_user;
use moodle_url;

class content_saver {

    /**
     * Zapisuje dokument do bazy danych i zwraca ID rekordu.
     *
     * @param array $params Parametry formularza.
     * @param \context_module $context
     * @param mixed $cm
     * @return int ID nowo zapisanego rekordu
     */
    public static function save(array $params, \context_module $context, mixed $cm): int {
        global $DB, $USER;

        //var_dump($params); die();//// Debugging line to check the parameters being passed.
        // Konwersja stdClass do cm_info, jeśli to konieczne.
        if ($cm instanceof \stdClass) {
            $modinfo = get_fast_modinfo($cm->course);
            $cm = $modinfo->get_cm($cm->id);
        }

        $record = self::prepare_record($params, $cm, $USER->id);
        $entryid = $DB->insert_record('valuemapdoc_content', $record);

        return $entryid;
    }

    private static function prepare_record(array $params, \cm_info $cm, int $userid): \stdClass {
        $record = new \stdClass();
        $record->courseid = $cm->course;
        $record->cmid = $cm->id;
        $record->userid = $userid;
        $record->templateid = $params['templateid'] ?? null;
        $record->customprompt = $params['customprompt'] ?? '(brak)';
        $record->opportunityname = $params['opportunityname'] ?? '(brak)';
        $record->content = /*base64_decode*/($params['documentcontent'] ?? '') ?: '(brak)';
        $record->timecreated = time();
        $record->effectiveness = $params['effectiveness'] ?? null;
        $record->feedback = $params['feedback'] ?? null;

        return $record;
    }
}