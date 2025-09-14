<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/local/field_levels.php');

use mod_valuemapdoc\local\field_levels;

class valuemapdoc_entry_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;
        $savetocopy = $customdata['savetocopy'] ?? false;
        $bulk_mode = $customdata['bulk_mode'] ?? false;
        $entries_count = $customdata['entries_count'] ?? 0;

        // Get user's field level and visible fields
        $user_fields = field_levels::get_user_fields();
        $user_level_config = field_levels::get_user_level_config();

        // Field level information
        if ($bulk_mode) {
            // Bulk edit header
            $bulk_info = get_string('bulk_edit_header', 'mod_valuemapdoc', $entries_count);
            $bulk_instruction = get_string('bulk_edit_instruction', 'mod_valuemapdoc');
            
            $mform->addElement('static', 'bulk_info', '', 
                '<div class="alert alert-primary"><h5>' . $bulk_info . '</h5>' . 
                '<p class="mb-0">' . $bulk_instruction . '</p></div>');
        }

        // Current level info with preferences link
        $level_info = get_string('current_editing_level', 'mod_valuemapdoc') . ': <strong>' . 
                     $user_level_config['name'] . '</strong> (' . $user_level_config['fields_count'] . ' ' . 
                     get_string('fields', 'mod_valuemapdoc') . ')';
        
        $preferences_url = new moodle_url('/mod/valuemapdoc/preferences.php', [
            'cmid' => $customdata['cmid'] ?? 0,
            'returnurl' => qualified_me()
        ]);
        
        $level_info .= ' <a href="' . $preferences_url->out() . '" class="btn btn-sm btn-outline-secondary">' .
                      '<i class="fa fa-cog"></i> ' . get_string('change_level', 'mod_valuemapdoc') . '</a>';
        
        $mform->addElement('static', 'level_info', '', 
            '<div class="alert alert-info mb-3">' . $level_info . '</div>');

        // All possible fields in the order they should appear
        $all_fields = [
            'market', 'industry', 'role', 'businessgoal', 'strategy', 'difficulty',
            'situation', 'statusquo', 'coi', 'differentiator', 'impact', 'newstate',
            'successmetric', 'impactstrategy', 'impactbusinessgoal', 'impactothers',
            'proof', 'time2results', 'quote', 'clientname'
        ];

        // Add form fields only for visible fields
        foreach ($all_fields as $field) {
            // Skip fields not visible for current user level
            if (!in_array($field, $user_fields)) {
                continue;
            }
            
            $label = get_string($field, 'mod_valuemapdoc');
            
            // In bulk mode, modify label to indicate it's optional
            if ($bulk_mode) {
                $label = get_string('bulk_field_label', 'mod_valuemapdoc', $label);
            }
            
            // Determine field type based on field name
            if (in_array($field, ['market', 'industry', 'role', 'time2results', 'clientname'])) {
                $mform->addElement('text', $field, $label);
                $mform->setType($field, PARAM_TEXT);
            } else {
                $mform->addElement('textarea', $field, $label, 'wrap="virtual" rows="3" cols="50"');
                $mform->setType($field, PARAM_TEXT);
            }
            
            $mform->addHelpButton($field, $field, 'mod_valuemapdoc');
            
            // In bulk mode, fields are not required
            if (!$bulk_mode) {
                // Add any field-specific requirements for normal mode here if needed
            }
        }

        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if (isset($customdata['id'])) {
            $mform->setDefault('id', $customdata['id']);
        }

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);

        $mform->addElement('hidden', 'entryid');
        $mform->setType('entryid', PARAM_INT);
        
        $mform->addElement('hidden', 'entryids');
        $mform->setType('entryids', PARAM_TEXT);
        
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        if (isset($customdata['cmid'])) {
            $mform->setDefault('cmid', $customdata['cmid']);
        }

        // Bulk mode specific hidden fields
        if ($bulk_mode) {
            $mform->addElement('hidden', 'bulk_mode');
            $mform->setType('bulk_mode', PARAM_BOOL);
            $mform->setDefault('bulk_mode', 1);
        }

        // Buttons
        $saveattrs = [];
        if ($savetocopy) {
            $saveattrs['disabled'] = 'disabled';
            $mform->addElement('text', $savetocopy, 'dddd');
            $mform->setType($savetocopy, PARAM_TEXT);
        }
        
        $buttonarray = [];
        
        if ($bulk_mode) {
            // Bulk mode buttons
            $buttonarray[] = $mform->createElement('submit', 'submitbutton', 
                get_string('update_selected_entries', 'mod_valuemapdoc'), 
                ['class' => 'btn btn-primary']);
            $buttonarray[] = $mform->createElement('cancel');
        } else {
            // Normal mode buttons
            $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'), $saveattrs);
            $buttonarray[] = $mform->createElement('submit', 'saveasnew', get_string('saveasnew', 'mod_valuemapdoc'));
            $buttonarray[] = $mform->createElement('cancel');
        }
        
        

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Set form data, ensuring all fields exist even if not visible
     *
     * @param array|object $default_values
     */
    public function set_data($default_values) {
        if (is_object($default_values)) {
            $default_values = (array) $default_values;
        }
        
        // Get all possible fields to ensure we handle all data
        $all_fields = field_levels::get_all_fields();
        
        // Ensure all field values are preserved even if not displayed
        foreach ($all_fields as $field) {
            if (isset($default_values[$field])) {
                // Field has data, keep it
                continue;
            }
        }
        
        parent::set_data($default_values);
    }

    /**
     * Get data for bulk update (only non-empty fields)
     *
     * @return array|false Array of fields to update or false if cancelled
     */
    public function get_bulk_data() {
        if (!$data = $this->get_data()) {
            return false;
        }
        
        $customdata = $this->_customdata;
        $bulk_mode = $customdata['bulk_mode'] ?? false;
        
        if (!$bulk_mode) {
            return false;
        }
        
        $user_fields = field_levels::get_user_fields();
        $bulk_data = [];
        
        // Only include fields that are not empty
        foreach ($user_fields as $field) {
            if (isset($data->$field) && trim($data->$field) !== '') {
                $bulk_data[$field] = trim($data->$field);
            }
        }
        
        return $bulk_data;
    }

    /**
     * Validation for bulk mode
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        $customdata = $this->_customdata;
        $bulk_mode = $customdata['bulk_mode'] ?? false;
        
        if ($bulk_mode) {
            // Check that at least one field is filled for bulk editing
            $user_fields = field_levels::get_user_fields();
            $has_data = false;
            
            foreach ($user_fields as $field) {
                if (!empty(trim($data[$field] ?? ''))) {
                    $has_data = true;
                    break;
                }
            }
            
            if (!$has_data) {
                $errors['level_info'] = get_string('no_fields_filled_bulk_edit', 'mod_valuemapdoc');
            }
        }
        
        return $errors;
    }
}