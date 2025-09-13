<?php
require_once("$CFG->libdir/formslib.php");

class valuemapdoc_entry_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;
        $savetocopy = $customdata['savetocopy'] ?? false;


        $fields = [
            'market', 'industry', 'role', 'businessgoal', 'strategy', 'difficulty',
            'situation', 'statusquo', 'coi', 'differentiator', 'impact', 'newstate',
            'successmetric', 'impactstrategy', 'impactbusinessgoal', 'impactothers',
            'proof', 'time2results', 'quote', 'clientname'
        ];

        foreach ($fields as $field) {
            $label = get_string($field, 'valuemapdoc');
            if (in_array($field, ['market', 'industry', 'role', 'time2results', 'clientname'])) {
                $mform->addElement('text', $field, $label);
                $mform->setType($field, PARAM_TEXT);
                $mform->addHelpButton($field, $field, 'valuemapdoc');
            } else {
                $mform->addElement('textarea', $field, $label, 'wrap="virtual" rows="3" cols="50"');
                $mform->setType($field, PARAM_TEXT);
                $mform->addHelpButton($field, $field, 'valuemapdoc');
            }
        }
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);

        $mform->addElement('hidden', 'entryid');
        $mform->setType('entryid', PARAM_INT);
        
        $mform->addElement('hidden', 'entryids');
        $mform->setType('entryids', PARAM_TEXT);

        $saveattrs = [];
        if ($savetocopy) {
            $saveattrs['disabled'] = 'disabled';
            $mform->addElement('text', $savetocopy, 'dddd');
            $mform->setType($savetocopy, PARAM_TEXT);

        }
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'), $saveattrs);
        $buttonarray[] = $mform->createElement('submit', 'saveasnew', get_string('saveasnew', 'mod_valuemapdoc'));
        $buttonarray[] = $mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
