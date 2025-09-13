<?php
namespace mod_valuemapdoc\form;

defined('MOODLE_INTERNAL') || die();

require_once('classes/local/template_helper.php');
require_once('classes/local/markets.php');

use mod_valuemapdoc\local\markets;
use mod_valuemapdoc\local\template_helper;

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class admin_template_form extends \moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT); 

        // Sekcja podstawowych informacji
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('templatename', 'mod_valuemapdoc'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Dodane pole description
        $mform->addElement('textarea', 'description', get_string('description', 'mod_valuemapdoc'), 
            'wrap="virtual" rows="3" cols="60"');
        $mform->setType('description', PARAM_TEXT);
        $mform->addHelpButton('description', 'description', 'mod_valuemapdoc');

        $existingtypes = $this->get_existing_themetype();
        $mform->addElement('autocomplete', 'templatetype', get_string('templatetype', 'mod_valuemapdoc'), 
            ($existingtypes), ['tags' => true]);
        $mform->setType('templatetype', PARAM_TEXT);
        $mform->addRule('templatetype', null, 'required', null, 'client');

        // Dodane pole fields
        $mform->addElement('textarea', 'fields', get_string('templatefields', 'mod_valuemapdoc'), 
            'wrap="virtual" rows="4" cols="60"');
        $mform->setType('fields', PARAM_TEXT);
        $mform->addHelpButton('fields', 'templatefields', 'mod_valuemapdoc');
        $mform->addElement('static', 'fieldshint', '', get_string('templatefields_help', 'mod_valuemapdoc'));

        // Sekcja treści szablonu
        $mform->addElement('header', 'templatecontent', get_string('templatecontent', 'mod_valuemapdoc'));

        $mform->addElement('editor', 'templatebody', get_string('templatebody', 'mod_valuemapdoc'), 
            null, array('maxfiles' => 0, 'maxbytes' => 0, 'trusttext' => true));
        $mform->addRule('templatebody', null, 'required', null, 'client');


        // Pierwszy checkbox - dokładnie jak w działającym teście
$mform->addElement('checkbox', 'show_template_help', 
    get_string('show_template_help', 'mod_valuemapdoc'));

// Pierwsza podpowiedź - upewnij się że jest ukryta domyślnie
$mform->addElement('static', 'templatebodyhint', '', 
    get_string('templatebody_help', 'mod_valuemapdoc'));
$mform->hideIf('templatebodyhint', 'show_template_help', 'notchecked');

// Drugi checkbox
$mform->addElement('checkbox', 'show_markets_help', 
    get_string('show_markets_help', 'mod_valuemapdoc'));

// Druga podpowiedź
$htmlmarketshelp = markets::generate_placeholders_help();
$mform->addElement('static', 'templatebodymarketshelp', '', $htmlmarketshelp);
$mform->hideIf('templatebodymarketshelp', 'show_markets_help', 'notchecked');



/*

        //
        // Grupa checkboxów dla pomocy
    $helpcheckboxes = array();
    $helpcheckboxes[] = $mform->createElement('checkbox', 'show_template_help', '', 
        'Instrukcje używania kodów {{pole}}');
    $helpcheckboxes[] = $mform->createElement('checkbox', 'show_markets_help', '', 
    'Lista dostępnych pól');

    $mform->addGroup($helpcheckboxes, 'help_options', 'Pokaż pomoc:', '<br/>', false);

    // Pierwsza podpowiedź
    $mform->addElement('static', 'templatebodyhint', '', 
        get_string('templatebody_help', 'mod_valuemapdoc'));
    $mform->hideIf('templatebodyhint', 'help_options[show_template_help]', 'notchecked');

    // Druga podpowiedź
    $htmlmarketshelp = markets::generate_placeholders_help();
    $mform->addElement('static', 'templatebodymarketshelp', '', $htmlmarketshelp);
    $mform->hideIf('templatebodymarketshelp', 'help_options[show_markets_help]', 'notchecked');



        // Checkbox do pokazywania pierwszej podpowiedzi
        $mform->addElement('advancedcheckbox', 'show_template_help', 
                get_string('show_template_help', 'mod_valuemapdoc'), 
                get_string('show_basic_help_desc', 'mod_valuemapdoc'));

 
        // Checkbox do pokazywania drugiej podpowiedzi
        $mform->addElement('advancedcheckbox', 'show_markets_help', 
            get_string('show_markets_help', 'mod_valuemapdoc'), 
            get_string('show_markets_help_desc', 'mod_valuemapdoc'));


        // Pierwsza podpowiedź - podstawowa pomoc
        $mform->addElement('static', 'templatebodyhint', '', 
            get_string('templatebody_help', 'mod_valuemapdoc'));
        $mform->hideIf('templatebodyhint', 'show_template_help', 'notchecked');

        // Druga podpowiedź - pomoc dla markets
        $htmlmarketshelp = markets::generate_placeholders_help();
        $mform->addElement('static', 'templatebodymarketshelp', '', $htmlmarketshelp);
        $mform->hideIf('templatebodymarketshelp', 'show_markets_help', 'notchecked');

//
        $mform->addElement('static', 'templatebodyhint', '', get_string('templatebody_help', 'mod_valuemapdoc'));

        $htmlmarketshelp = markets::generate_placeholders_help();
        $mform->addElement('static', 'templatebodymarketshelp', '', $htmlmarketshelp);
*/
        $mform->addElement('textarea', 'prompt', get_string('templateprompt', 'mod_valuemapdoc'), 
            'wrap="virtual" rows="4" cols="60"');
        $mform->setType('prompt', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Walidacja formularza
     * @param array $data tablica danych z formularza
     * @param array $files tablica plików
     * @return array tablica błędów walidacji
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

       /* // Walidacja pola fields - sprawdzenie czy zawiera prawidłowe pola
        if (!empty($data['fields'])) {
            $fields_array = explode(',', $data['fields']);
            foreach ($fields_array as $field) {
                $field = trim($field);
                if (!empty($field) && !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) {
                    $errors['fields'] = get_string('invalidfieldname', 'mod_valuemapdoc', $field);
                    break;
                }
            }
        } */

        return $errors;
    }

    private function get_existing_themetype() {
        global $DB;
    
        $options = \mod_valuemapdoc\template_helper::get_templates_list();
        return $options;
    }
}