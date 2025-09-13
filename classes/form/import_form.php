<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class mod_valuemapdoc_import_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('filepicker', 'csvfile', get_string('file'), null, ['accepted_types' => '.csv']);
        $mform->addRule('csvfile', null, 'required');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('import'));
    }
}