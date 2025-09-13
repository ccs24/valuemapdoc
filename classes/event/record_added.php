<?php
namespace mod_valuemapdoc\event;

defined('MOODLE_INTERNAL') || die();

class record_added extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'valuemapdoc_entries'; // <- TO JEST KLUCZOWE!
    }

    public static function get_name() {
        return get_string('eventrecord_added', 'mod_valuemapdoc');
    }

    public function get_description() {
        return "The user with id '{\$this->userid}' triggered: User added a value map entry. Object ID: '{\$this->objectid}'.";
    }

    public function get_url() {
        return new \moodle_url('/mod/valuemapdoc/view.php', ['id' => $this->contextinstanceid]);
    }
}
