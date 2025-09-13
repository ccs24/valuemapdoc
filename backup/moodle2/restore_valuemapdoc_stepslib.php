<?php
defined('MOODLE_INTERNAL') || die();

class restore_valuemapdoc_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure() {
        $paths = [];

        $paths[] = new restore_path_element('valuemapdoc', '/activity/valuemapdoc');
        $paths[] = new restore_path_element('valuemapdoc_entry', '/activity/valuemapdoc/entries/entry');
        $paths[] = new restore_path_element('valuemapdoc_content', '/activity/valuemapdoc/contents/content');
        $paths[] = new restore_path_element('valuemapdoc_template', '/activity/valuemapdoc/templates/template');

        return $paths;
    }

    protected function process_valuemapdoc($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('valuemapdoc', $data);

        $this->apply_activity_instance($newitemid);
    }

    protected function process_valuemapdoc_entry($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->cid = $this->get_new_parentid('valuemapdoc');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('valuemapdoc_entries', $data);
    }

    protected function process_valuemapdoc_content($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->cid = $this->get_new_parentid('valuemapdoc');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('valuemapdoc_content', $data);
    }

    protected function process_valuemapdoc_template($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->cid = $this->get_new_parentid('valuemapdoc');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('valuemapdoc_templates', $data);
    }

    protected function after_execute() {
        $this->add_related_files('mod_valuemapdoc', 'intro', null);
    }
}