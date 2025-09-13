<?php
namespace mod_valuemapdoc\form;

use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

use moodleform;

class edit_content_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;


        $mform->addElement('editor', 'workingcontent', get_string('editcontent', 'mod_valuemapdoc'), null, 
            ['maxfiles' => 0,
            'class' => 'editor',
            ]); 
        $mform->setType('workingcontent', PARAM_RAW);

        // Hidden fields to keep track of origin and parameters.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Hidden fields to keep track of origin and parameters.
        $mform->addElement('hidden', 'docid');
        $mform->setType('docid', PARAM_INT);
        $mform->setDefault('docid', $customdata['docid']);

        // Dodajemy własne przyciski
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'tunecontent', get_string('tuning', 'mod_valuemapdoc'),
            ['class' => 'btn btn-primary', 'id' => 'tunecontentbutton']
        );
        $buttonarray[] = $mform->createElement('submit', 'savecontent', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);

    }
}