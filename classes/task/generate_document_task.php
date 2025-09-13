<?php
namespace mod_valuemapdoc\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/valuemapdoc/classes/local/generator.php');
require_once($CFG->dirroot.'/mod/valuemapdoc/classes/local/openai_client.php');
require_once($CFG->dirroot.'/mod/valuemapdoc/classes/local/prompt_logger.php');
require_once($CFG->dirroot.'/mod/valuemapdoc/classes/local/session_helper.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/local/markets.php');

use mod_valuemapdoc\local\generator;
use mod_valuemapdoc\local\openai_client;
use mod_valuemapdoc\local\session_helper;
use mod_valuemapdoc\local\prompt_logger;
use mod_valuemapdoc\local\markets;
use context_module;

class generate_document_task extends \core\task\adhoc_task {

    public function execute() {
        global $DB;

        // OPTYMALIZACJA 1: Zwiększ limity czasowe i pamięciowe
        @set_time_limit(300); // 5 minut
        @ini_set('memory_limit', '512M');
        
        // OPTYMALIZACJA 2: Dodaj timeout dla OpenAI
        @ini_set('default_socket_timeout', 120); // 2 minuty timeout dla HTTP

        $data = $this->get_custom_data();
        $contentid = $data->contentid ?? null;

        mtrace("🚀 Starting document generation task. ContentID: " . $contentid);
        $start_time = microtime(true);

        // OPTYMALIZACJA 3: Szybka walidacja i ustawienie statusu na 'pending'
        if (!$contentid) {
            mtrace("❌ Missing contentid, aborting task");
            return;
        }

        // Oznacz dokument jako w trakcie generowania
        try {
            $DB->set_field('valuemapdoc_content', 'status', 'pending', ['id' => $contentid]);
            mtrace("✅ Status set to 'pending'");
        } catch (Exception $e) {
            mtrace("❌ Failed to set status: " . $e->getMessage());
            return;
        }

        $courseid = $data->courseid ?? null;
        $context = context_module::instance( $data->cmid );
        $id = $data->cmid ?? null;
        $groupid = $data->groupid ?? null;
        $userid = $data->userid ?? null;
        $entryids = $data->entryids ?? [];
        $templateid = $data->templateid ?? '';
        $marketid = $data->marketid ?? 0;
        $customerid = $data->customerid ?? 0;
        $personid = $data->personid  ?? 0;
        $opportunityid = $data->opportunityid ?? 0;

        if (!$userid || empty($entryids)) {
            $this->mark_as_error($contentid, "Missing userid or entryids");
            return;
        }

        try {
            $cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
        } catch (Exception $e) {
            $this->mark_as_error($contentid, "Invalid course module: " . $e->getMessage());
            return;
        }

        // OPTYMALIZACJA 4: Batch loading entries
        mtrace("📥 Loading entries...");
        list($inSql, $inParams) = $DB->get_in_or_equal($entryids, SQL_PARAMS_NAMED);
//        $params = $inParams + ['userid' => $userid];
//        $entries = $DB->get_records_select('valuemapdoc_entries', "id $inSql AND userid = :userid", $params);
        $entries = $DB->get_records_select('valuemapdoc_entries', "id $inSql", $inParams);

        if (empty($entries)) {
            $this->mark_as_error($contentid, "No entries found");
            return;
        }

        mtrace("✅ Loaded " . count($entries) . " entries");



        // OPTYMALIZACJA 5: Cache template loading
        mtrace("📄 Loading template...");
        try {
            $template = generator::load_template_by_id((int)$templateid);
//            $templateprompt = (is_object($template) && isset($template->prompt)) ? $template->prompt : '';
            $templateprompt = (!empty($template) && property_exists($template, 'prompt')) ? $template->prompt : '';
            $templatebody = (is_object($template) && isset($template->templatebody)) ? $template->templatebody : '';
        } catch (Exception $e) {
            $this->mark_as_error($contentid, "Template loading failed: " . $e->getMessage());
            return;
        }

         mtrace("✅ TEMPLATE PROMPT " . $templateprompt);
//         mtrace("✅ TEMPLATE BODY " . $templatebody);

        // OPTYMALIZACJA 6: Pre-generate content structure
        mtrace("🔧 Generating document structure...");
        try {
//            $documentcontent = generator::generate_document(array_values($entries), $templatebody);
            $documentcontent = $templatebody;
            $sourceknowledge = generator::format_entries_for_prompt(array_values($entries));
        } catch (Exception $e) {
            $this->mark_as_error($contentid, "Document structure generation failed: " . $e->getMessage());
            return;
        }

        // OPTYMALIZACJA 6.5: loading Marktets data
        mtrace("📄 Loading markets data...");
        try {
/*            
            $market = markets::export_for_ai($marketid);
            $customer = markets::export_for_ai($customerid);
            $person = markets::export_for_ai($personid);
            $opportunity = markets::export_for_ai($opportunityid);
            $templatebody = markets::replace_placeholders($templatebody, $market, $customer, $person, $opportunity); */

            $templatebody = markets::replace_placeholders_from_db($templatebody, $marketid, $customerid, $personid, $opportunityid);

                    // wymuszenie filtrów
        


        } catch (Exception $e) {
            $this->mark_as_error($contentid, "Markets placeholder replace failed: " . $e->getMessage());
            return;
        }



        // OPTYMALIZACJA 7: Build prompt efficiently
        mtrace("💬 Building prompt...");
        $systemprompt = get_config('mod_valuemapdoc', 'default_system_prompt');
        $templateintro = get_config('mod_valuemapdoc', 'default_template_prompt');
        
        try {
            $moduleinstance = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
            $activity_prompt = $moduleinstance->activity_prompt ?? '';
        } catch (Exception $e) {
            $this->mark_as_error($contentid, "Module instance not found: " . $e->getMessage());
            return;
        }

        mtrace("✅ TEMPLATE BODY " . $templatebody);


        $fullprompt = implode("\n\n", array_filter([
            "activity_prompt:\n" . $activity_prompt,
            "templateintro:\n" . $templateintro,
            "templateprompt:\n" . $templateprompt,
            "templatebody:\n" . $templatebody,
            "sourceknowledge:\n" . $sourceknowledge
        ]));

        // Dodaj to:
        mtrace("📋 FULL PROMPT:");
        mtrace("=" . str_repeat("=", 80));
        mtrace($fullprompt);
        mtrace("=" . str_repeat("=", 80));

        mtrace("🤖 Calling OpenAI API... (prompt length: " . strlen($fullprompt) . " chars)");

        // OPTYMALIZACJA 8: Retry mechanism for OpenAI
        $max_retries = 3;
        $retry_delay = 5; // seconds
        $response = null;

        for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
            try {
                mtrace("🔄 Attempt $attempt/$max_retries...");
                
                // OPTYMALIZACJA 9: Add request timeout
                $response = openai_client::generate_text3($fullprompt, $systemprompt, [
                    'timeout' => 120, // 2 minutes timeout
                    'max_tokens' => 4000 // Limit response length
                ]);
                
                if (!empty($response)) {
                    mtrace("✅ OpenAI response received (length: " . strlen($response) . " chars)");
                    break;
                }
                
            } catch (\Exception $e) {
                mtrace("⚠️ Attempt $attempt failed: " . $e->getMessage());
                
                if ($attempt < $max_retries) {
                    mtrace("⏳ Waiting $retry_delay seconds before retry...");
                    sleep($retry_delay);
                    $retry_delay *= 2; // Exponential backoff
                } else {
                    // Log final error
                    $promptid = prompt_logger::log_prompt_response(
                        $userid,
                        $courseid,
                        $templateid ?? 0,
                        $marketid ?? 0, 
                        $customerid ?? 0, 
                        $personid ?? 0, 
                        $opportunityid ?? 0,
                        $systemprompt . "|" . $fullprompt,
                        'ERROR: ' . $e->getMessage(),
                        null
                    );
                    
                    $this->mark_as_error($contentid, "OpenAI API failed after $max_retries attempts: " . $e->getMessage());
                    return;
                }
            }
        }

        if (empty($response)) {
            $promptid = prompt_logger::log_prompt_response(
                $userid,
                $courseid,
                $templateid ?? 0,
                $marketid ?? 0, 
                $customerid ?? 0, 
                $personid ?? 0, 
                $opportunityid ?? 0,
                $systemprompt . "|" . $fullprompt,
                'ERROR: no_response_from_openai',
                null
            );
            
            $this->mark_as_error($contentid, "No response from OpenAI");
            return;
        }

        // Log successful response
        $promptid = prompt_logger::log_prompt_response(
            $userid,
            $courseid,
            $templateid ?? 0,
            $marketid ?? 0, 
            $customerid ?? 0, 
            $personid ?? 0, 
            $opportunityid ?? 0,
            $systemprompt . "|" . $fullprompt,
            $response,
            null
        );

        $options = [
            'context' => $context,
            'filter' => true,  // Wymusza wszystkie włączone filtry
        ];
//        $templatebody = format_text($templatebody, FORMAT_HTML, $options);
        $response = format_text($response, FORMAT_HTML, $options);
        

        // OPTYMALIZACJA 10: Transaction for final update
        mtrace("💾 Saving document...");
        $transaction = $DB->start_delegated_transaction();
        
        try {
            $record = $DB->get_record('valuemapdoc_content', ['id' => $contentid], '*', MUST_EXIST);
            $record->content = $response;
            $record->timemodified = time();
            $record->status = 'ready';
            $record->customprompt = $fullprompt;
/*            $marketid, 
            $customerid, 
            $personid, 
            $opportunityid, */
//            $record->opportunityname = $opportunityname;

            $DB->update_record('valuemapdoc_content', $record);
            $transaction->allow_commit();
            
        } catch (Exception $e) {
            $transaction->rollback($e);
            $this->mark_as_error($contentid, "Database update failed: " . $e->getMessage());
            return;
        }

        $end_time = microtime(true);
        $execution_time = round($end_time - $start_time, 2);
        
        mtrace("🎉 Document generated successfully! Execution time: {$execution_time}s");
        mtrace("📊 Response length: " . strlen($response) . " characters");
    }

    /**
     * Mark document as error and log the issue
     * @param int $contentid
     * @param string $error_message
     */
    private function mark_as_error($contentid, $error_message) {
        global $DB;
        
        mtrace("❌ Error: " . $error_message);
        
        try {
            $DB->set_field('valuemapdoc_content', 'status', 'error', ['id' => $contentid]);
            $DB->set_field('valuemapdoc_content', 'content', 
                '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' . 
                htmlspecialchars($error_message) . '</div>', 
                ['id' => $contentid]
            );
        } catch (Exception $e) {
            mtrace("❌ Failed to mark as error: " . $e->getMessage());
        }
    }
}
