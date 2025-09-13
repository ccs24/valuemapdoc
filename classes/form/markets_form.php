<?php
namespace mod_valuemapdoc\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
use moodleform;
use mod_valuemapdoc\local\markets;

class markets_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;
        
        // DEBUG - sprawdź co przychodzi do formularza
        if (debugging()) {
 //           $mform->addElement('html', '<div style="background: pink; padding: 10px;"><h3>DEBUG FORM CUSTOMDATA:</h3><pre>' . print_r($customdata, true) . '</pre></div>');
        }
        
        // Pobierz kontekst - typ jest ustalany automatycznie na podstawie URL/parentid
        $type = $customdata['type'] ?? 'market';
        $parentid = $customdata['parentid'] ?? 0;
        $parent_name = $customdata['parent_name'] ?? '';
        $parent_type = $customdata['parent_type'] ?? '';
        $is_edit = $customdata['is_edit'] ?? false;
        $record = $customdata['record'] ?? null;
        $recordid = $customdata['recordid'] ?? null;
        $id = $customdata['id'] ?? null;
        $cmid = $customdata['cmid'] ?? $id; // DODANE: cmid
        $courseid = $customdata['courseid'] ?? 0;
        
        // KRYTYCZNE: Hidden fields - wszystkie wymagane pola
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $courseid);
        
        // DODANE: cmid field
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->setDefault('cmid', $cmid);
        
        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_ALPHA);
        $mform->setDefault('type', $type);
        
        $mform->addElement('hidden', 'parentid');
        $mform->setType('parentid', PARAM_INT);
        $mform->setDefault('parentid', $parentid);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id);
        
        // POPRAWIONE: action field
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHA);
        $mform->setDefault('action', $is_edit ? 'edit' : 'add');

        if ($is_edit && $recordid) {
            $mform->addElement('hidden', 'recordid');
            $mform->setType('recordid', PARAM_INT);
            $mform->setDefault('recordid', $recordid);
        }
        
        // Kontekstowy opis - pokazuje użytkownikowi gdzie dodaje element
        if (!empty($parent_name)) {
            $context_info = $this->get_context_description($type, $parent_type, $parent_name);
            $mform->addElement('static', 'context_info', '', 
                '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . $context_info . '</div>');
        }
        
        // Sekcja podstawowych informacji
        $mform->addElement('header', 'basic_info', $this->get_section_title($type, $is_edit));
        
        // Dynamiczne etykiety pól podstawowych
        $labels = $this->get_field_labels($type);
        
        $mform->addElement('text', 'name', $labels['name'], ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');
        
        $mform->addElement('textarea', 'description', $labels['description'], 
                          ['rows' => 4, 'cols' => 60]);
        $mform->setType('description', PARAM_TEXT);

        // NOWE: Dodaj dynamiczne pola JSON dla tego typu
        $this->add_dynamic_fields($type, $record);
        
        // Dynamiczny tekst przycisku
        $button_text = $this->get_button_text($type, $is_edit);
        $this->add_action_buttons(true, $button_text);
        
        // DEBUG - pokaż jak wygląda formularz
        if (debugging()) {
           // $mform->addElement('html', '<div style="background: lightcyan; padding: 10px;"><h3>FORM DEFINITION COMPLETE</h3></div>');
        }
    }

    /**
     * Dodaj dynamiczne pola JSON z kontekstowymi etykietami
     */
    private function add_dynamic_fields($type, $record = null) {
        $mform = $this->_form;
        
        // Pobierz konfigurację pól dla tego typu
        $fields_config = markets::get_fields_for_type($type);
        
        if (empty($fields_config)) {
            return; // Brak dodatkowych pól dla tego typu
        }

        // Nagłówek sekcji z kontekstową nazwą
        $section_title = $this->get_additional_fields_title($type);
        $mform->addElement('header', 'additional_header', $section_title);

        // Sortuj pola według order
        uasort($fields_config, function($a, $b) {
            return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
        });

        // Dodaj każde pole
        foreach ($fields_config as $field_name => $field_config) {
            $element_name = 'field_' . $field_name;
            $label = $field_config['label'];
            $description = $field_config['description'] ?? '';

            switch ($field_config['type']) {
                case 'text':
                    $mform->addElement('text', $element_name, $label, ['size' => 60]);
                    $mform->setType($element_name, PARAM_TEXT);
                    break;

                case 'textarea':
                    $mform->addElement('textarea', $element_name, $label, ['rows' => 3, 'cols' => 60]);
                    $mform->setType($element_name, PARAM_TEXT);
                    break;

                case 'select':
                    $options = ['' => get_string('choose')] + array_combine($field_config['options'], $field_config['options']);
                    $mform->addElement('select', $element_name, $label, $options);
                    break;
            }

            // Dodaj opis/pomoc
            if (!empty($description)) {
                $mform->addElement('static', $element_name . '_help', '', 
                    '<small class="text-muted">' . htmlspecialchars($description) . '</small>');
            }

            // Ustaw jako wymagane jeśli potrzeba
            if ($field_config['required'] ?? false) {
                $mform->addRule($element_name, get_string('required'), 'required');
            }

            // Ustaw domyślną wartość jeśli edytujemy
            if ($record) {
                $record = markets::parse_dynamic_fields($record);
                $default_value = markets::get_field_value($record, $field_name);
                if (!empty($default_value)) {
                    $mform->setDefault($element_name, $default_value);
                }
            }
        }
    }
    
    /**
     * Pobierz kontekstowy opis tego co użytkownik robi
     */
    private function get_context_description($type, $parent_type, $parent_name) {
        $icons = [
            'market' => '🔍',
            'customer' => '🏛',
            'person' => '👤',
            'opportunity' => '💰'
        ];
        
        $type_icon = $icons[$type] ?? '📄';
        $parent_icon = $icons[$parent_type] ?? '📄';
        
        switch ($type) {
            case 'customer':
                return sprintf('Adding customer %s to market: %s <strong>%s</strong>', 
                    $type_icon, $parent_icon, $parent_name);
            case 'person':
                return sprintf('Adding person %s to customer: %s <strong>%s</strong>', 
                    $type_icon, $parent_icon, $parent_name);
            case 'opportunity':
                return sprintf('Adding opportunity %s to customer: %s <strong>%s</strong>', 
                    $type_icon, $parent_icon, $parent_name);
            default:
                return sprintf('Adding new market %s', $type_icon);
        }
    }
    
    /**
     * Pobierz tytuł sekcji podstawowej
     */
    private function get_section_title($type, $is_edit) {
        $action = $is_edit ? 'Edit' : 'Add';
        
        switch ($type) {
            case 'customer':
                return $action . ' Customer Information';
            case 'person':
                return $action . ' Person Information';
            case 'opportunity':
                return $action . ' Opportunity Information';
            default:
                return $action . ' Market Information';
        }
    }
    
    /**
     * Pobierz tytuł sekcji dodatkowych pól
     */
    private function get_additional_fields_title($type) {
        switch ($type) {
            case 'customer':
                return 'Customer Details';
            case 'person':
                return 'Person Details';
            case 'opportunity':
                return 'Opportunity Details';
            case 'market':
                return 'Market Analysis';
            default:
                return 'Additional Information';
        }
    }
    
    /**
     * Pobierz etykiety pól podstawowych (name, description)
     */
    private function get_field_labels($type) {
        switch ($type) {
            case 'customer':
                return [
                    'name' => 'Customer Name',
                    'description' => 'Customer Description'
                ];
            case 'person':
                return [
                    'name' => 'Person Name',
                    'description' => 'Person Details & Background'
                ];
            case 'opportunity':
                return [
                    'name' => 'Opportunity Name',
                    'description' => 'Opportunity Description'
                ];
            default:
                return [
                    'name' => 'Market Name',
                    'description' => 'Market Description'
                ];
        }
    }
    
    /**
     * Pobierz tekst przycisku
     */
    private function get_button_text($type, $is_edit) {
        if ($is_edit) {
            return 'Update ' . ucfirst($type);
        }
        
        switch ($type) {
            case 'customer':
                return 'Add Customer';
            case 'person':
                return 'Add Person';
            case 'opportunity':
                return 'Add Opportunity';
            default:
                return 'Add Market';
        }
    }
    
    /**
     * Walidacja formularza - POPRAWIONA
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        // Podstawowa walidacja nazwy
        if (empty(trim($data['name']))) {
            $errors['name'] = get_string('required');
            return $errors;
        }
        
        // Pobierz parametry - POPRAWIONE: używa cmid zamiast courseid
        $type = $data['type'] ?? '';
        $parentid = $data['parentid'] ?? 0;
        $recordid = $data['recordid'] ?? 0; // POPRAWIONE: dla edycji
        $cmid = $data['cmid'] ?? 0; // POPRAWIONE: używa cmid
        
        // Sprawdź walidację hierarchii
        if (!markets::validate_hierarchy($type, $parentid)) {
            $errors['general'] = 'Invalid parent-child relationship';
        }
        
        // POPRAWIONE: Sprawdź duplikaty nazw używając cmid
        if (!empty($type) && !empty($cmid)) {
            if (markets::name_exists($data['name'], $type, $cmid, $parentid, $recordid)) {
                $errors['name'] = 'A ' . $type . ' with this name already exists in this context';
            }
        }
        
        // Walidacja dynamicznych pól JSON
        $dynamic_field_data = [];
        $fields_config = markets::get_fields_for_type($type);
        
        foreach ($fields_config as $field_name => $field_config) {
            $form_field_name = 'field_' . $field_name;
            if (isset($data[$form_field_name])) {
                $dynamic_field_data[$field_name] = $data[$form_field_name];
            }
        }

        $validation_errors = markets::validate_field_data($type, $dynamic_field_data);
        foreach ($validation_errors as $field_name => $error) {
            $errors['field_' . $field_name] = $error;
        }
        
        return $errors;
    }
    
    /**
     * Ustaw dane formularza z parsowaniem pól JSON
     */
    public function set_data($data) {
        if (is_object($data)) {
            // Parse dynamic fields if editing
            $data = markets::parse_dynamic_fields($data);
        }
        
        parent::set_data($data);
    }
}