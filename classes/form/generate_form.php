<?php
require_once("$CFG->libdir/formslib.php");

class valuemapdoc_generate_form extends moodleform {
    public function definition() {
        global $DB, $USER; 

        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'] ?? 0;
        $entryids = $this->_customdata['entryids'] ?? [];


        $mform->addElement('header', 'general', get_string('generatedocument', 'mod_valuemapdoc'));

        /*
        foreach ($entryids as $entryid) {
            $mform->addElement('hidden', 'entryids[]', $entryid);
            $mform->setType('entryids[]', PARAM_INT);
        }
            */

        foreach (array_values($entryids) as $i => $entryid) {
            $mform->addElement('hidden', "entryids[$i]", $entryid);
            $mform->setType("entryids[$i]", PARAM_INT);
        }

 
        // Podsumowanie zaznaczonych wpisów z bazy
        if (!empty($entryids)) {
            list($sql, $params) = $DB->get_in_or_equal($entryids, SQL_PARAMS_QM);
            $entries = $DB->get_records_select('valuemapdoc_entries', "id $sql", $params);
            $summary = '';
            foreach ($entries as $entry) {
                $summary .= html_writer::tag('div',
                    html_writer::tag('strong', format_string($entry->role)) . ' — ' .
                    format_string($entry->businessgoal) . ' (' . format_string($entry->difficulty) . ')'
                );
            }
            $mform->addElement('static', 'selectedsummary', get_string('selectedentries', 'mod_valuemapdoc'), $summary);
        }




        // Autocomplete z istniejącymi nazwami szans
        $mform->addElement('autocomplete', 'opportunityname', get_string('opportunityname', 'mod_valuemapdoc'), $this->get_existing_opportunities($USER->id), ['tags' => true]);
        $mform->setType('opportunityname', PARAM_TEXT);
        $mform->addRule('opportunityname', null, 'required', null, 'client');

//        $mform->addElement('hidden', 'opportunityname', 'sales');
//        $mform->setType('opportunityname', PARAM_TEXT);


        // Pobierz wszystkie szablony
        $alltemplates = $DB->get_records('valuemapdoc_templates', null, '', 'id, name, templatetype');

        // Zbuduj listę typów
        $templatetypes = [];
        foreach ($alltemplates as $tpl) {
            if (!empty($tpl->templatetype)) {
                $templatetypes[$tpl->templatetype] = $tpl->templatetype;
            }
        }

        $mform->addElement('select', 'templatetype', get_string('templatetype', 'valuemapdoc'), $templatetypes);
        $mform->setType('templatetype', PARAM_TEXT);
        $mform->addRule('templatetype', null, 'required', null, 'client');

        // Pole z nazwami szablonów – będzie uzupełniane JS-em
        $templateoptions = [];
        foreach ($alltemplates as $tpl) {
            $templateoptions[$tpl->id] = $tpl->name;
        }
        $mform->addElement('select', 'templateid', get_string('templatename', 'valuemapdoc'), $templateoptions);
        $mform->setType('templateid', PARAM_INT);
        $mform->addRule('templateid', null, 'required', null, 'client');

        // Ukryte pole z wszystkimi szablonami jako JSON (dla JS)
        $templatejson = [];
        foreach ($alltemplates as $tpl) {
            $templatejson[] = ['id' => $tpl->id, 'name' => $tpl->name, 'type' => $tpl->templatetype];
        }

        $mform->addElement('html', html_writer::script('
            const templateData = ' . json_encode($templatejson) . ';
            document.addEventListener("DOMContentLoaded", () => {
                const typeSelect = document.querySelector("select[name=templatetype]");
                const nameSelect = document.querySelector("select[name=templateid]");

                function updateTemplateList() {
                    const selectedType = typeSelect.value;
                    nameSelect.innerHTML = "<option value=\'\'>" + " -- Select a template -- " + "</option>";
                    templateData
                        .filter(t => t.type === selectedType)
                        .forEach(t => {
                            const opt = document.createElement("option");
                            opt.value = t.id;
                            opt.textContent = t.name;
                            nameSelect.appendChild(opt);
                        });
                    if (nameSelect.options.length > 1) {
                        nameSelect.selectedIndex = 1;
                    }
                    console.log("Available templates:", templateData.filter(t => t.type === selectedType));
                }

                typeSelect.addEventListener("change", updateTemplateList);
                updateTemplateList();
            });
        '));

        // Checkbox do pokazania własnego promptu.
        $mform->addElement('advcheckbox', 'advancedsettings', '', get_string('advancedsettings', 'mod_valuemapdoc'));
        $mform->setType('advancedsettings', PARAM_BOOL);

        // Pole textarea na prompt.
        $mform->addElement('textarea', 'customprompt', get_string('customprompt', 'valuemapdoc'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('customprompt', PARAM_TEXT);
        $mform->setDefault('customprompt', '');
        $mform->addHelpButton('customprompt', 'customprompt', 'valuemapdoc');

        // Ukryj textarea dopóki checkbox nie jest zaznaczony.
        $mform->hideIf('customprompt', 'advancedsettings', 'notchecked');


        $mform->addElement('hidden', 'id', $this->_customdata['cmid'] ?? 0);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('generatedocument', 'mod_valuemapdoc'));
    }

    /*
    public function get_data() {
        $data = parent::get_data();

        if ($data && isset($data->entryids)) {
            $decoded = json_decode($data->entryids, true);
            $data->entryids = is_array($decoded) ? array_map('intval', $decoded) : [];
        }

        return $data;
    }
*/
    private function get_existing_opportunities($userid) {
        global $DB;
//        $records = $DB->get_records_sql("SELECT DISTINCT opportunityname FROM {valuemapdoc_content} WHERE userid = ?", [$userid]);
        $records = $DB->get_fieldset_select('valuemapdoc_content', 'DISTINCT opportunityname', 'userid = ?', [$userid]);
        if (empty($records)) {
            return [];
        }
        // Zwróć unikalne nazwy szans jako tablicę
        $options = [];
        foreach ($records as $opportunityname) {
            if (!empty($opportunityname)) {
                $options[$opportunityname] = $opportunityname;
            }
        }
        return $options;
        
    }
}
