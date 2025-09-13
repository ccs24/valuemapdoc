<?php 

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/valuemapdoc/backup/moodle2/restore_valuemapdoc_stepslib.php');

class restore_valuemapdoc_activity_task extends restore_activity_task {
    protected function define_my_settings() {
        // Brak niestandardowych ustawień
    }

    protected function define_my_steps() {
        $this->add_step(new restore_valuemapdoc_activity_structure_step('valuemapdoc_structure', 'valuemapdoc.xml'));
    }

    public static function define_decode_contents() {
        return [
            new restore_decode_content('valuemapdoc', ['intro'], 'valuemapdoc')
        ];
    }

    public static function define_decode_rules() {
        return [
            new restore_decode_rule('VALUEMAPDOCVIEW', '/mod/valuemapdoc/view.php?id=$1', 'course_module')
        ];
    }
}