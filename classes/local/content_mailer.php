<?php

namespace mod_valuemapdoc\local;

defined('MOODLE_INTERNAL') || die();

use core_user;
use stdClass;
use html_writer;

class content_mailer {

    /**
     * Send an email to the user with the saved content.
     *
     * @param stdClass $user The user object to send the email to.
     * @param string $opportunityname The name of the opportunity.
     * @param string $content The content to be sent in the email.
     * @param int $templateid The ID of the template used for the content.
     */
    public static function send_by_entryid(int $entryid): void {
        global $DB,$USER;

        $record = $DB->get_record('valuemapdoc_content', ['id' => $entryid], '*', MUST_EXIST);
//        $user = $DB->get_record('user', ['id' => $record->userid], '*', MUST_EXIST);
 
        self::send_document_notification(
            $USER,
            $record->opportunityname ?? '',
            $record->content ?? '',
            $record->templateid ?? 0
        );
    }

    /**
     * Send an email to the user with the saved content.
     *
     * @param stdClass $user The user object to send the email to.
     * @param string $opportunityname The name of the opportunity.
     * @param string $content The content to be sent in the email.
     * @param int $templateid The ID of the template used for the content.
     */
    public static function send_document_notification(stdClass $user, string $opportunityname, string $content, int $templateid = 0): void {
        global $PAGE;

        $templateinfo = self::get_template_info($templateid);

        $titleparts = [];
        if (!empty($opportunityname)) {
            $titleparts[] = $opportunityname;
        }
        if (!empty($templateinfo['templatetype'])) {
            $titleparts[] = $templateinfo['templatetype'];
        }
        if (!empty($templateinfo['templatename'])) {
            $titleparts[] = $templateinfo['templatename'];
        }

        $subject = get_string('emailsubject', 'mod_valuemapdoc');
        $subject = 'VM:';
        if (!empty($titleparts)) {
            $subject .= ' ' . implode(' | ', $titleparts);
        }


        // Ensure $context is available for get_role_users().
       
        $context = $PAGE->context ?? context_system::instance();
        // Get first teacher of the course as sender.
        $teachers = get_role_users(3, $context); // 3 = editingteacher
        $supportuser = $teachers ? reset($teachers) : core_user::get_support_user();

        $messagehtml = $content;
        $messagehtml = \html_writer::tag('div', $content, ['style' => 'margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px;']);
        $messageplaintext = strip_tags($content);

        $noreply = core_user::get_noreply_user();
        email_to_user($user, $supportuser, $subject, $messageplaintext, $messagehtml);

    }

    

    /**
     * Get template information based on the template ID.
     *
     * @param int $templateid The ID of the template.
     * @return array An array containing the template type and name.
     */

    private static function get_template_info(int $templateid): array {
        global $DB;

        if (!$templateid) {
            return ['templatetype' => '(brak)', 'templatename' => '(brak)'];
        }

        $record = $DB->get_record('valuemapdoc_templates', ['id' => $templateid], 'templatetype, name');
        return [
            'templatetype' => $record->templatetype ?? '(brak)',
            'templatename' => $record->name ?? '(brak)'
        ];
    }

}