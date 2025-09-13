<?php
namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

//require_once('classes/local/prompt_logger.php');
//require_once('classes/local/session_helper.php');


use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

use mod_valuemapdoc\local\session_helper;
use mod_valuemapdoc\local\prompt_logger;
use mod_valuemapdoc\local\openai_client;

class tune_content_api extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'originaltext' => new external_value(PARAM_RAW, 'Original content to tune'),
            'prompt' => new external_value(PARAM_TEXT, 'Tuning prompt'),
            'docid' => new external_value(PARAM_INT, 'Document ID', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute($originaltext, $prompt,$docid) {
        global $USER, $COURSE, $DB;;

        self::validate_parameters(self::execute_parameters(), [
            'originaltext' => $originaltext,
            'prompt' => $prompt,
            'docid' => $docid,
        ]);

        $document = $DB->get_record('valuemapdoc_content', ['id' => $docid], '*', MUST_EXIST);
        $systemprompt = get_config('mod_valuemapdoc', 'default_system_prompt');

        $fullprompt = $originaltext . "\n" . $prompt;

        try {
            $response = openai_client::generate_text2($fullprompt, $systemprompt);
        } catch (\Exception $e) {
            $promptid = \mod_valuemapdoc\local\prompt_logger::log_prompt_response( 
                $USER->id, //userid
                $COURSE->id, //courseid
                $document->templateid, //templateid
                $document->marketid, 
                $document->customerid, 
                $document->personid, 
                $document->opportunityid,
                $prompt, //prompt
                'ERROR: ' . $e->getMessage(), //response
                null, //\mod_valuemapdoc\local\session_helper::get('promptid') //parentid
            );
        
           
            throw new \moodle_exception('openai_api_error', 'mod_valuemapdoc', '', null, $e->getMessage());
        }

        if (empty($response)) {
            $promptid = \mod_valuemapdoc\local\prompt_logger::log_prompt_response(
                $USER->id, //userid
                $COURSE->id, //courseid
                $document->templateid, //templateid
                $document->marketid, 
                $document->customerid, 
                $document->personid, 
                $document->opportunityid,
                $prompt, //prompt
                'ERROR: ' . 'no_response_from_openai', //response
                null, //\mod_valuemapdoc\local\session_helper::get('promptid') //parentid
            );
        
            throw new \moodle_exception('no_response_from_openai', 'mod_valuemapdoc');
        }


        $promptid = \mod_valuemapdoc\local\prompt_logger::log_prompt_response(
            $USER->id, //userid
            $COURSE->id, //courseid
            $document->templateid, //templateid
            $document->marketid, 
            $document->customerid, 
            $document->personid, 
            $document->opportunityid,
            $prompt, //prompt
            $response, //response
            null, //\mod_valuemapdoc\local\session_helper::get('promptid') //parentid
        );
    
        // Update current prompt id in session.
 //       \mod_valuemapdoc\local\session_helper::set('promptid', $promptid);


        $tuned = $response;

        return [
            'tunedtext' => $tuned
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'tunedtext' => new external_value(PARAM_RAW, 'Tuned document content')
        ]);
    }
}