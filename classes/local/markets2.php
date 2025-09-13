<?php
namespace mod_valuemapdoc\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for managing markets, customers, opportunities and persons with dynamic JSON fields
 *
 * @package    mod_valuemapdoc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class markets {
    
    /** @var string Market type constant */
    const TYPE_MARKET = 'market';
    
    /** @var string Customer type constant */
    const TYPE_CUSTOMER = 'customer';
    
    /** @var string Opportunity type constant */
    const TYPE_OPPORTUNITY = 'opportunity';
    
    /** @var string Person type constant */
    const TYPE_PERSON = 'person';

    /**
     * Get field configuration for each type
     * This is where you define what fields each type should have
     *
     * @return array Configuration array
     */
    public static function get_field_configuration() {
        return [
            self::TYPE_MARKET => [
                'basic_info' => [
                    'label' => 'Market Characteristics',
                    'description' => 'Primary market characteristics and definition',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 1
                ],
                'external_trends' => [
                    'label' => 'Market Trends (External)',
                    'description' => 'External market trends and influences',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 2
                ],
                'current_approach' => [
                    'label' => 'Current Market Approach',
                    'description' => 'Current market approach not supporting our offer',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 3
                ],
                'market_size' => [
                    'label' => 'Market Size',
                    'description' => 'Estimated market size and potential',
                    'type' => 'text',
                    'required' => false,
                    'order' => 4
                ],
                'competition_level' => [
                    'label' => 'Competition Level',
                    'description' => 'Level of competition in this market',
                    'type' => 'select',
                    'options' => ['Low', 'Medium', 'High', 'Very High'],
                    'required' => false,
                    'order' => 5
                ]
            ],
            
            self::TYPE_CUSTOMER => [
                'industry' => [
                    'label' => 'Industry',
                    'description' => 'Customer industry or sector',
                    'type' => 'text',
                    'required' => true,
                    'order' => 1
                ],
                'external_triggers' => [
                    'label' => 'External Triggers',
                    'description' => 'External factors driving customer needs',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 2
                ],
                'internal_initiatives' => [
                    'label' => 'Internal Initiatives',
                    'description' => 'Customer internal initiatives and projects',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 3
                ],
                'company_size' => [
                    'label' => 'Company Size',
                    'description' => 'Number of employees',
                    'type' => 'select',
                    'options' => ['1-10', '11-50', '51-200', '201-1000', '1000+'],
                    'required' => false,
                    'order' => 4
                ],
                'annual_revenue' => [
                    'label' => 'Annual Revenue',
                    'description' => 'Estimated annual revenue',
                    'type' => 'text',
                    'required' => false,
                    'order' => 5
                ],
                'decision_process' => [
                    'label' => 'Decision Process',
                    'description' => 'How decisions are made in this organization',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 6
                ]
            ],
            
            self::TYPE_PERSON => [
                'role' => [
                    'label' => 'Role',
                    'description' => 'Person role, position or responsibilities',
                    'type' => 'text',
                    'required' => true,
                    'order' => 1
                ],
                'opportunities_seen' => [
                    'label' => 'Opportunities',
                    'description' => 'Opportunities this person sees',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 2
                ],
                'risks_challenges' => [
                    'label' => 'Risks & Challenges',
                    'description' => 'Risks and challenges they face',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 3
                ],
                'influence_level' => [
                    'label' => 'Influence Level',
                    'description' => 'Level of influence in decision making',
                    'type' => 'select',
                    'options' => ['Low', 'Medium', 'High', 'Decision Maker'],
                    'required' => false,
                    'order' => 4
                ],
                'contact_preference' => [
                    'label' => 'Contact Preference',
                    'description' => 'Preferred way of communication',
                    'type' => 'select',
                    'options' => ['Email', 'Phone', 'In Person', 'LinkedIn', 'Other'],
                    'required' => false,
                    'order' => 5
                ],
                'motivation' => [
                    'label' => 'Motivation',
                    'description' => 'What motivates this person',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 6
                ]
            ],
            
            self::TYPE_OPPORTUNITY => [
                'opportunity_type' => [
                    'label' => 'Opportunity Type',
                    'description' => 'Type or product category',
                    'type' => 'text',
                    'required' => true,
                    'order' => 1
                ],
                'market_context' => [
                    'label' => 'Market Context',
                    'description' => 'External market context and environment',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 2
                ],
                'internal_readiness' => [
                    'label' => 'Internal Readiness',
                    'description' => 'Our internal readiness and preparation',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 3
                ],
                'estimated_value' => [
                    'label' => 'Estimated Value',
                    'description' => 'Expected deal value',
                    'type' => 'text',
                    'required' => false,
                    'order' => 4
                ],
                'probability' => [
                    'label' => 'Success Probability',
                    'description' => 'Probability of closing this deal',
                    'type' => 'select',
                    'options' => ['10%', '25%', '50%', '75%', '90%'],
                    'required' => false,
                    'order' => 5
                ],
                'timeline' => [
                    'label' => 'Timeline',
                    'description' => 'Expected timeline to close',
                    'type' => 'select',
                    'options' => ['1 month', '3 months', '6 months', '1 year', '1+ years'],
                    'required' => false,
                    'order' => 6
                ],
                'competition' => [
                    'label' => 'Competition',
                    'description' => 'Competing solutions or vendors',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 7
                ]
            ]
        ];
    }

    /**
     * Get fields for specific type
     *
     * @param string $type Record type
     * @return array Field configuration for the type
     */
    public static function get_fields_for_type($type) {
        $config = self::get_field_configuration();
        return $config[$type] ?? [];
    }

    /**
     * Get required fields for type
     *
     * @param string $type Record type
     * @return array Required field names
     */
    public static function get_required_fields($type) {
        $fields = self::get_fields_for_type($type);
        $required = [];
        
        foreach ($fields as $field_name => $field_config) {
            if ($field_config['required'] ?? false) {
                $required[] = $field_name;
            }
        }
        
        return $required;
    }

    /**
     * Validate field data for type
     *
     * @param string $type Record type
     * @param array $field_data Field data to validate
     * @return array Validation errors (empty if valid)
     */
    public static function validate_field_data($type, $field_data) {
        $errors = [];
        $fields_config = self::get_fields_for_type($type);
        
        foreach ($fields_config as $field_name => $field_config) {
            $value = $field_data[$field_name] ?? '';
            
            // Check required fields
            if (($field_config['required'] ?? false) && empty(trim($value))) {
                $errors[$field_name] = get_string('field_required', 'mod_valuemapdoc', $field_config['label']);
            }
            
            // Validate select field options
            if ($field_config['type'] === 'select' && !empty($value)) {
                $valid_options = $field_config['options'] ?? [];
                if (!in_array($value, $valid_options)) {
                    $errors[$field_name] = get_string('invalid_option', 'mod_valuemapdoc');
                }
            }
        }
        
        return $errors;
    }

    /**
     * Create new market/customer entry with JSON fields
     *
     * @param \stdClass $data Entry data
     * @return int New record ID
     * @throws \dml_exception
     */
    public static function create($data) {
        global $DB, $USER;
        
        // Validate hierarchy before creating
        if (!self::validate_hierarchy($data->type, $data->parentid)) {
            throw new \moodle_exception('invalid_hierarchy', 'mod_valuemapdoc');
        }

        // Prepare dynamic fields JSON
        $dynamic_fields = [];
        $fields_config = self::get_fields_for_type($data->type);
        
        foreach ($fields_config as $field_name => $field_config) {
            if (isset($data->{'field_' . $field_name})) {
                $dynamic_fields[$field_name] = $data->{'field_' . $field_name};
            }
        }

        // Validate dynamic fields
        $validation_errors = self::validate_field_data($data->type, $dynamic_fields);
        if (!empty($validation_errors)) {
            throw new \moodle_exception('validation_failed', 'mod_valuemapdoc', '', implode(', ', $validation_errors));
        }
        
        $record = new \stdClass();
        $record->type = $data->type;
        $record->name = trim($data->name);
        $record->description = $data->description ?? '';
        $record->dynamic_fields = json_encode($dynamic_fields); // Store as JSON
        $record->courseid = $data->courseid;
        $record->userid = $data->userid ?? $USER->id;
        $record->groupid = $data->groupid ?? 0;
        $record->parentid = $data->parentid ?? 0;
        $record->isactive = 1;
        $record->timecreated = time();
        $record->timemodified = time();
        
        // Check for duplicates
        if (self::name_exists($record->name, $record->type, $record->courseid, $record->parentid)) {
            throw new \moodle_exception('duplicate_name', 'mod_valuemapdoc');
        }
        
        return $DB->insert_record('valuemapdoc_markets', $record);
    }

    /**
     * Update existing entry with JSON fields
     *
     * @param int $id Record ID
     * @param \stdClass $data Updated data
     * @return bool Success
     * @throws \dml_exception
     */
    public static function update($id, $data) {
        global $DB;
        
        $record = $DB->get_record('valuemapdoc_markets', ['id' => $id]);
        if (!$record) {
            throw new \moodle_exception('record_not_found', 'mod_valuemapdoc');
        }
        
        // Validate hierarchy before updating
        if (!self::validate_hierarchy($data->type, $data->parentid)) {
            throw new \moodle_exception('invalid_hierarchy', 'mod_valuemapdoc');
        }

        // Prepare dynamic fields JSON
        $dynamic_fields = [];
        $fields_config = self::get_fields_for_type($data->type);
        
        foreach ($fields_config as $field_name => $field_config) {
            if (isset($data->{'field_' . $field_name})) {
                $dynamic_fields[$field_name] = $data->{'field_' . $field_name};
            }
        }

        // Validate dynamic fields
        $validation_errors = self::validate_field_data($data->type, $dynamic_fields);
        if (!empty($validation_errors)) {
            throw new \moodle_exception('validation_failed', 'mod_valuemapdoc', '', implode(', ', $validation_errors));
        }
        
        // Check for duplicates (excluding current record)
        if (self::name_exists($data->name, $data->type, $record->courseid, $data->parentid ?? 0, $id)) {
            throw new \moodle_exception('duplicate_name', 'mod_valuemapdoc');
        }
        
        $record->name = trim($data->name);
        $record->description = $data->description ?? '';
        $record->dynamic_fields = json_encode($dynamic_fields); // Update JSON
        $record->type = $data->type;
        $record->parentid = $data->parentid ?? 0;
        $record->timemodified = time();
        
        return $DB->update_record('valuemapdoc_markets', $record);
    }

    /**
     * Get record by ID with parsed JSON fields
     *
     * @param int $id Record ID
     * @return \stdClass|false Record with parsed fields or false if not found
     */
    public static function get_by_id($id) {
        global $DB;
        $record = $DB->get_record('valuemapdoc_markets', ['id' => $id, 'isactive' => 1]);
        
        if ($record) {
            $record = self::parse_dynamic_fields($record);
        }
        
        return $record;
    }

    /**
     * Get records by type with parsed JSON fields
     *
     * @param int $courseid Course ID
     * @param string $type Type filter (empty for all types)
     * @param int $userid User ID filter (0 for all users)
     * @param int $groupid Group ID filter (0 for all groups)
     * @return array Array of market records with parsed fields
     */
    public static function get_by_type($courseid, $type = '', $userid = 0, $groupid = 0) {
        global $DB;
        
        $conditions = [
            'courseid' => $courseid,
            'isactive' => 1
        ];
        
        if (!empty($type)) {
            $conditions['type'] = $type;
        }
        
        if ($userid > 0) {
            $conditions['userid'] = $userid;
        }
        
        if ($groupid > 0) {
            $conditions['groupid'] = $groupid;
        }
        
        $records = $DB->get_records('valuemapdoc_markets', $conditions, 'name ASC');
        
        // Parse JSON fields for all records
        foreach ($records as &$record) {
            $record = self::parse_dynamic_fields($record);
        }
        
        return $records;
    }

    /**
     * Parse dynamic fields JSON into object properties
     *
     * @param \stdClass $record Record with JSON fields
     * @return \stdClass Record with parsed fields
     */
    public static function parse_dynamic_fields($record) {
        if (!empty($record->dynamic_fields)) {
            $dynamic_fields = json_decode($record->dynamic_fields, true);
            if (is_array($dynamic_fields)) {
                foreach ($dynamic_fields as $field_name => $field_value) {
                    $record->{'field_' . $field_name} = $field_value;
                }
            }
        }
        
        return $record;
    }

    /**
     * Get field value for record
     *
     * @param \stdClass $record Record object
     * @param string $field_name Field name
     * @return string Field value
     */
    public static function get_field_value($record, $field_name) {
        return $record->{'field_' . $field_name} ?? '';
    }

    /**
     * Render additional fields for display
     *
     * @param \stdClass $record Record with parsed fields
     * @return string HTML output
     */
    public static function render_dynamic_fields($record) {
        $html = '';
        $fields_config = self::get_fields_for_type($record->type);
        
        if (empty($fields_config)) {
            return $html;
        }

        // Sort fields by order
        uasort($fields_config, function($a, $b) {
            return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
        });

        $has_content = false;
        foreach ($fields_config as $field_name => $field_config) {
            $value = self::get_field_value($record, $field_name);
            if (!empty(trim($value))) {
                $has_content = true;
                break;
            }
        }

        if (!$has_content) {
            return '';
        }

        $html .= '<div class="dynamic-fields mt-3">';
        
        foreach ($fields_config as $field_name => $field_config) {
            $value = self::get_field_value($record, $field_name);
            
            if (!empty(trim($value))) {
                $html .= '<div class="dynamic-field mb-2">';
                $html .= '<strong>' . htmlspecialchars($field_config['label']) . ':</strong> ';
                
                if ($field_config['type'] === 'textarea') {
                    $html .= '<div class="text-muted mt-1">' . nl2br(htmlspecialchars($value)) . '</div>';
                } else {
                    $html .= '<span class="text-muted">' . htmlspecialchars($value) . '</span>';
                }
                
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get form elements for dynamic fields
     *
     * @param \moodleform $mform Form object
     * @param string $type Record type
     * @param \stdClass|null $record Existing record (for editing)
     */
    public static function add_dynamic_fields_to_form($mform, $type, $record = null) {
        $fields_config = self::get_fields_for_type($type);
        
        if (empty($fields_config)) {
            return;
        }

        // Sort by order
        uasort($fields_config, function($a, $b) {
            return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
        });

        $mform->addElement('header', 'dynamic_fields', get_string('additional_information', 'mod_valuemapdoc'));

        foreach ($fields_config as $field_name => $field_config) {
            $element_name = 'field_' . $field_name;
            $label = $field_config['label'];
            $description = $field_config['description'] ?? '';

            switch ($field_config['type']) {
                case 'text':
                    $mform->addElement('text', $element_name, $label);
                    $mform->setType($element_name, PARAM_TEXT);
                    break;

                case 'textarea':
                    $mform->addElement('textarea', $element_name, $label, 'rows="4" cols="60"');
                    $mform->setType($element_name, PARAM_TEXT);
                    break;

                case 'select':
                    $options = [''] + array_combine($field_config['options'], $field_config['options']);
                    $mform->addElement('select', $element_name, $label, $options);
                    break;
            }

            // Add help text
            if (!empty($description)) {
                $mform->addElement('static', $element_name . '_help', '', 
                    '<small class="text-muted">' . $description . '</small>');
            }

            // Set as required if needed
            if ($field_config['required'] ?? false) {
                $mform->addRule($element_name, get_string('required'), 'required', null, 'client');
            }

            // Set default value if editing
            if ($record) {
                $default_value = self::get_field_value($record, $field_name);
                if (!empty($default_value)) {
                    $mform->setDefault($element_name, $default_value);
                }
            }
        }
    }

    // [Keep all other existing methods from the original class unchanged...]
    // get_all_available_types(), get_hierarchy_rules(), get_allowed_children(), etc.

    // Rest of the original methods remain the same...
    public static function get_types() {
        return [
            self::TYPE_MARKET => get_string('type_market', 'mod_valuemapdoc'),
            self::TYPE_CUSTOMER => get_string('type_customer', 'mod_valuemapdoc'),
            self::TYPE_OPPORTUNITY => get_string('type_opportunity', 'mod_valuemapdoc'),
            self::TYPE_PERSON => get_string('type_person', 'mod_valuemapdoc')
        ];
    }
    
    public static function get_hierarchy_rules() {
        return [
            self::TYPE_MARKET => null,                    
            self::TYPE_CUSTOMER => self::TYPE_MARKET,     
            self::TYPE_OPPORTUNITY => self::TYPE_CUSTOMER, 
            self::TYPE_PERSON => self::TYPE_CUSTOMER      
        ];
    }
    
    public static function get_allowed_children($type) {
        $rules = self::get_hierarchy_rules();
        $children = [];
        
        foreach ($rules as $child_type => $parent_type) {
            if ($parent_type === $type) {
                $children[] = $child_type;
            }
        }
        
        return $children;
    }
}