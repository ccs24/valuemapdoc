<?php
namespace mod_valuemapdoc\event;

defined('MOODLE_INTERNAL') || die();

class document_generated extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'valuemapdoc_entries'; // <- TO JEST KLUCZOWE!
    }

    public static function get_name() {
        return get_string('eventdocument_generated', 'mod_valuemapdoc');
    }

    public function get_description() {
        return "The user with id '{\$this->userid}' triggered: User generated a value map document. Object ID: '{\$this->objectid}'.";
    }

    public function get_url() {
        return new \moodle_url('/mod/valuemapdoc/view.php', ['id' => $this->contextinstanceid]);
    }
}
