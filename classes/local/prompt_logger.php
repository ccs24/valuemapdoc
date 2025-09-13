<?php
namespace mod_valuemapdoc\local;
/*
 * This file is part of the Moodle Value Map Document module.
 *
 * @package    mod_valuemapdoc
 * @category   local
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class prompt_logger {
    /**
     * Log the prompt and response from OpenAI.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @param int $templateid Template ID
     * @param string|null $opportunity Opportunity
     * @param string $prompt Prompt sent to OpenAI
     * @param string $response Response received from OpenAI
     */
    public static function log_prompt_response(int $userid, int $courseid, int $templateid, 
            int  $market, int  $customer, int  $person, int  $opportunity,
            string $prompt, ?string $response, ?int $parentid = null) {
        global $DB;
        $record = new \stdClass();
        $record->userid = $userid;
        $record->courseid = $courseid;
        $record->templateid = $templateid;
        $record->market = $market;
        $record->customer = $customer;
        $record->person = $person;
        $record->opportunity = $opportunity;
        $record->prompt = $prompt;
        $record->response = $response ?? '';
//        $record->rating = $rating ;
//        $record->feedback = $feedback;
        $record->parentid = $parentid ?? null;
        $record->timecreated = time();

        return $DB->insert_record('valuemapdoc_promptlog', $record);
    }
}