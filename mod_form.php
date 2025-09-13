<?php
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_valuemapdoc_mod_form extends moodleform_mod {
    public function definition() {
        $mform = $this->_form;


        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        //-------------------------------------------------------


        // Główna sekcja ustawień
        $mform->addElement('header', 'generalsettings', get_string('generalsettings', 'mod_valuemapdoc'));

        // Wybór folderu docelowego (dynamiczny)
        $folders = $this->get_available_folders((int) $this->current->course);
        $folders[0] =  get_string('output_userfolder', 'mod_valuemapdoc');

        $mform->addElement('select', 'targetactivity', get_string('targetactivity', 'mod_valuemapdoc'), $folders);
        $mform->setType('targetactivity', PARAM_INT);
        $mform->addRule('targetactivity', null, 'required', null, 'client');
        $mform->setDefault('targetactivity',0);

        $mform->addElement('advcheckbox', 'ismaster', get_string('ismaster', 'mod_valuemapdoc'), get_string('ismaster_label', 'mod_valuemapdoc'));
        $mform->setDefault('ismaster', 0);
        $mform->setType('ismaster', PARAM_BOOL);


        // Sekcja z ustawieniami dla ChatGPT
        $mform->addElement('header', 'chatgptsettings', get_string('chatgptsettings', 'mod_valuemapdoc'));

        $mform->addElement('textarea', 'activity_prompt', get_string('activity_prompt', 'mod_valuemapdoc'), 'wrap="virtual" rows="8" cols="60"');
        $mform->setType('activity_prompt', PARAM_TEXT);
        $mform->addHelpButton('activity_prompt', 'activity_prompt', 'mod_valuemapdoc');

        $this->standard_coursemodule_elements();


        // Standardowe przyciski (Zapisz i wróć, Zapisz i pokaż)
        $this->add_action_buttons();
    }

    /**
     * Zwraca listę dostępnych aktywności typu folder (i podobnych) do zapisu plików.
     */
    private function get_available_folders(int $courseid): array {
        global $DB;

        $supportedmodules = [
            'folder' => 'folder',
            'publication' => 'publication',
            'assign' => 'assign',
            'workshop' => 'workshop',
        ];
    
        $options = [];
    
        foreach ($supportedmodules as $modname => $tablename) {
            // Sprawdź, czy moduł jest zainstalowany
            if (!$DB->record_exists('modules', ['name' => $modname])) {
                continue;
            }
    
            $sql = "SELECT cm.id, cm.instance, m.name AS modname, inst.name AS instancename
                      FROM {course_modules} cm
                      JOIN {modules} m ON m.id = cm.module
                      JOIN {{$tablename}} inst ON inst.id = cm.instance
                     WHERE cm.course = ? AND m.name = ? AND cm.deletioninprogress = 0";
    
            $records = $DB->get_records_sql($sql, [$courseid, $modname]);
    
            foreach ($records as $rec) {
                $options[$rec->id] = get_string('modulename', 'mod_' . $rec->modname) . ': ' . $rec->instancename;
            }
        }
    
        if (empty($options)) {
            $options[0] = get_string('nofolderavailable', 'mod_valuemapdoc');
        }
    
        return $options;
    }
}
