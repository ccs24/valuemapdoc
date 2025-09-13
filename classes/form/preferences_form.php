<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_valuemapdoc\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;
use mod_valuemapdoc\local\field_levels;

/**
 * Form for user field level preferences
 *
 * @package    mod_valuemapdoc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preferences_form extends moodleform {

    /**
     * Define the form
     */
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Header
        $mform->addElement('header', 'preferences_header', get_string('field_level_preferences', 'mod_valuemapdoc'));

        // Current level info
        $current_level = field_levels::get_user_level();
        $level_config = field_levels::get_user_level_config();
        
        $current_info = get_string('current_level', 'mod_valuemapdoc') . ': <strong>' . 
                       $level_config['name'] . '</strong> (' . $level_config['fields_count'] . ' ' . 
                       get_string('fields', 'mod_valuemapdoc') . ')';
        
        $mform->addElement('static', 'current_level_info', '', 
            '<div class="alert alert-info">' . $current_info . '</div>');

        // Level selection
        $levels = field_levels::get_levels();
        $level_options = [];
        
        foreach ($levels as $key => $config) {
            $level_options[$key] = $config['name'];
        }
        
        $mform->addElement('select', 'field_level', get_string('select_field_level', 'mod_valuemapdoc'), $level_options);
        $mform->setDefault('field_level', $current_level);
        $mform->addHelpButton('field_level', 'select_field_level', 'mod_valuemapdoc');

        // Level descriptions with field lists
        foreach ($levels as $key => $config) {
            $description_html = '<div class="level-description mb-3 p-3 border rounded">';
            $description_html .= '<h5>' . $config['name'] . '</h5>';
            $description_html .= '<p class="text-muted">' . $config['description'] . '</p>';
            $description_html .= '<p><strong>' . get_string('fields_included', 'mod_valuemapdoc') . ' (' . $config['fields_count'] . '):</strong></p>';
            $description_html .= '<div class="row">';
            
            $field_count = 0;
            foreach ($config['fields'] as $field) {
                if ($field_count % 2 == 0 && $field_count > 0) {
                    $description_html .= '</div><div class="row">';
                }
                
                $field_label = get_string($field, 'mod_valuemapdoc');
                $description_html .= '<div class="col-md-6"><small>• ' . $field_label . '</small></div>';
                $field_count++;
            }
            
            $description_html .= '</div></div>';
            
            $mform->addElement('static', 'level_' . $key . '_desc', '', $description_html);
            $mform->hideIf('level_' . $key . '_desc', 'field_level', 'neq', $key);
        }

        // Hidden fields
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        if (isset($customdata['cmid'])) {
            $mform->setDefault('cmid', $customdata['cmid']);
        }
        
        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_URL);
        if (isset($customdata['returnurl'])) {
            $mform->setDefault('returnurl', $customdata['returnurl']);
        }

        // Buttons
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'savepreferences', 
            get_string('save_preferences', 'mod_valuemapdoc'), ['class' => 'btn btn-primary']);
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('cancel'));

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Validation
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        // Validate field level
        if (!empty($data['field_level'])) {
            $levels = field_levels::get_levels();
            if (!array_key_exists($data['field_level'], $levels)) {
                $errors['field_level'] = get_string('invalid_field_level', 'mod_valuemapdoc');
            }
        }
        
        return $errors;
    }
}