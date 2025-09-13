<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/valuemapdoc/backup/moodle2/backup_valuemapdoc_stepslib.php');

class backup_valuemapdoc_activity_task extends backup_activity_task {
    protected function define_my_settings() {
        // Brak niestandardowych ustawień
    }

    protected function define_my_steps() {
        $this->add_step(new backup_valuemapdoc_activity_structure_step('valuemapdoc_structure', 'valuemapdoc.xml'));
    }

    public static function encode_content_links($content) {
        global $CFG;
        $base = preg_quote($CFG->wwwroot, '/');

        return preg_replace(
            "/({$base}\/mod\/valuemapdoc\/view.php\?id=)([0-9]+)/",
            '$@VALUEMAPDOCVIEW*$2@$',
            $content
        );
    }
}