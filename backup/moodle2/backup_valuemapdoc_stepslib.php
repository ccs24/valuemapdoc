<?php

defined('MOODLE_INTERNAL') || die(); 

require_once($CFG->dirroot . '/mod/valuemapdoc/backup/moodle2/backup_valuemapdoc_stepslib.php');

class backup_valuemapdoc_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $valuemapdoc = new backup_nested_element('valuemapdoc', ['id'], [
            'name', 'intro', 'introformat', 'targetactivity','ismaster', 
            'activity_prompt', 'groupid', 'timemodified'
        ]);

        $entries = new backup_nested_element('entries');
        $entry = new backup_nested_element('entry', ['id'], [
            'cid', 'course', 'userid', 'timemodified', 'market', 'industry', 'role', 'businessgoal',
            'strategy', 'difficulty', 'situation', 'statusquo', 'coi', 'differentiator',
            'impact', 'newstate','successmetric', 'impactstrategy',
            'impactbusinessgoal', 'impactothers', 'proof', 'time2results',
            'quote', 'clientname', 
            'ismaster', 'maturity','groupid'
        ]);

        $valuemapdoc->add_child($entries);
        $entries->add_child($entry);

        // Additional nested elements for contents and templates.
        $contents = new backup_nested_element('contents');
        $content = new backup_nested_element('content', ['id'], [
            'cid', 'userid', 'templatetype', 'templatename', 'input', 'output', 'rating', 'feedback', 'timemodified'
        ]);

        $templates = new backup_nested_element('templates');
        $template = new backup_nested_element('template', ['id'], [
            'cid', 'userid', 'templatetype', 'templatename', 'prompt', 'timemodified'
        ]);

        $valuemapdoc->add_child($contents);
        $contents->add_child($content);

        $valuemapdoc->add_child($templates);
        $templates->add_child($template);

        $valuemapdoc->set_source_table('valuemapdoc', ['id' => backup::VAR_ACTIVITYID]);
        $entry->set_source_table('valuemapdoc_entries', ['cid' => backup::VAR_PARENTID]);
        $content->set_source_table('valuemapdoc_content', ['cid' => backup::VAR_PARENTID]);
        $template->set_source_table('valuemapdoc_templates', ['cid' => backup::VAR_PARENTID]);

        $entry->annotate_ids('user', 'userid');
        $content->annotate_ids('user', 'userid');
        $template->annotate_ids('user', 'userid');

        return $this->prepare_activity_structure($valuemapdoc);
    }
}