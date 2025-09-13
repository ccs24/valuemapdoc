<?php
namespace mod_valuemapdoc\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for managing markets, customers, opportunities and persons
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
                'market_info' => [
                    'label' => 'Market',
                    'description' => 'Market name',
                    'type' => 'text',
                    'required' => true,
                    'order' => 1
                ],
                'basic_info' => [
                    'label' => 'Market Characteristics',
                    'description' => 'Primary market characteristics and definition',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 2
                ],
                'external_trends' => [
                    'label' => 'Market Trends (External)',
                    'description' => 'External market trends and influences',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 3
                ],
                'current_approach' => [
                    'label' => 'Current Market Approach',
                    'description' => 'Current market approach not supporting our offer',
                    'type' => 'textarea',
                    'required' => false,
                    'order' => 4
                ],
                'market_size' => [
                    'label' => 'Market Size',
                    'description' => 'Estimated market size and potential',
                    'type' => 'text',
                    'required' => false,
                    'order' => 5
                ],
                'competition_level' => [
                    'label' => 'Competition Level',
                    'description' => 'Level of competition in this market',
                    'type' => 'select',
                    'options' => ['Low', 'Medium', 'High', 'Very High'],
                    'required' => false,
                    'order' => 6
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
                    'label' => 'Opportunity product',
                    'description' => 'Product category',
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
     * Get all available types
     *
     * @return array Array of type => display name
     */
    public static function get_types() {
        return [
            self::TYPE_MARKET => get_string('type_market', 'mod_valuemapdoc'),
            self::TYPE_CUSTOMER => get_string('type_customer', 'mod_valuemapdoc'),
            self::TYPE_OPPORTUNITY => get_string('type_opportunity', 'mod_valuemapdoc'),
            self::TYPE_PERSON => get_string('type_person', 'mod_valuemapdoc')
        ];
    }
    
    /**
     * Get hierarchy structure - what can be parent of what
     *
     * @return array Array of child_type => parent_type
     */
    public static function get_hierarchy_rules() {
        return [
            self::TYPE_MARKET => null,                    // Market has no parent
            self::TYPE_CUSTOMER => self::TYPE_MARKET,     // Customer's parent is Market
            self::TYPE_OPPORTUNITY => self::TYPE_CUSTOMER, // Opportunity's parent is Customer
            self::TYPE_PERSON => self::TYPE_CUSTOMER      // Person's parent is Customer
        ];
    }
    
    /**
     * Get allowed children for a type
     *
     * @param string $type Parent type
     * @return array Array of allowed child types
     */
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
 * UŻYWA cmid zamiast valuemapdocid
 *
 * @param int $cmid Course Module ID
 * @param string $type Type filter (empty for all types)
 * @param int $userid User ID filter (0 for all users)
 * @param int $groupid Group ID filter (0 for all groups)
 * @return array Array of market records with parsed fields
 */
public static function get_by_type($cmid, $type = '', $userid = 0, $groupid = 0) {
    global $DB;
    
    $conditions = [
        'cmid' => $cmid,
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
    
    /**
     * Get records for a specific parent
     *
     * @param int $parentid Parent ID
     * @param string $type Child type filter (optional)
     * @return array Child records
     */
    public static function get_children($parentid, $type = '') {
        global $DB;
        
        $conditions = [
            'parentid' => $parentid, 
            'isactive' => 1
        ];
        
        if (!empty($type)) {
            $conditions['type'] = $type;
        }
        
        return $DB->get_records('valuemapdoc_markets', $conditions, 'type ASC, name ASC');
    }
    
    /**
     * Get all children recursively
     *
     * @param int $parentid Parent ID
     * @return array All descendant records
     */
    public static function get_all_children($parentid) {
        global $DB;
        
        $children = [];
        $direct_children = $DB->get_records('valuemapdoc_markets', 
                                           ['parentid' => $parentid, 'isactive' => 1]);
        
        foreach ($direct_children as $child) {
            $children[] = $child;
            // Recursively get children of this child
            $grandchildren = self::get_all_children($child->id);
            $children = array_merge($children, $grandchildren);
        }
        
        return $children;
    }
    
    /**
     * Get breadcrumb path for a record
     *
     * @param int $record_id Record ID
     * @return array Breadcrumb array from root to record
     */
    public static function get_breadcrumb($record_id) {
        global $DB;
        
        $breadcrumb = [];
        $current_id = $record_id;
        
        while ($current_id > 0) {
            $record = $DB->get_record('valuemapdoc_markets', ['id' => $current_id]);
            if (!$record) break;
            
            array_unshift($breadcrumb, $record);
            $current_id = $record->parentid;
        }
        
        return $breadcrumb;
    }
    
/**
 * Create new market/customer entry with JSON fields
 * UŻYWA cmid
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
    $record->dynamic_fields = json_encode($dynamic_fields);
    
    // UŻYWA cmid z danych formularza
    $record->cmid = $data->cmid ?? $data->id; // fallback na 'id' z formularza
    $record->courseid = $data->courseid; // zachowaj dla referencji
    
    $record->userid = $data->userid ?? $USER->id;
    $record->groupid = $data->groupid ?? 0;
    $record->parentid = $data->parentid ?? 0;
    $record->isactive = 1;
    $record->timecreated = time();
    $record->timemodified = time();
    
    // Check for duplicates w ramach course module
    if (self::name_exists($record->name, $record->type, $record->cmid, $record->parentid)) {
        throw new \moodle_exception('duplicate_name', 'mod_valuemapdoc');
    }
    
    return $DB->insert_record('valuemapdoc_markets', $record);
}

/**
 * Update existing entry with JSON fields
 * POPRAWIONE: sprawdza duplikaty w ramach instancji modułu
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
    
    // POPRAWIONE: sprawdź duplikaty w ramach instancji modułu (excluding current record)
    if (self::name_exists($data->name, $data->type, $record->cmid, $data->parentid ?? 0, $id)) {
        throw new \moodle_exception('duplicate_name', 'mod_valuemapdoc');
    }
    
    $record->name = trim($data->name);
    $record->description = $data->description ?? '';
    $record->dynamic_fields = json_encode($dynamic_fields);
    $record->type = $data->type;
    $record->parentid = $data->parentid ?? 0;
    $record->timemodified = time();
    
    return $DB->update_record('valuemapdoc_markets', $record);
}

    
    /**
     * Delete entry (soft delete)
     *
     * @param int $id Record ID
     * @return bool Success
     */
    public static function delete($id) {
        global $DB;
        
        return $DB->update_record('valuemapdoc_markets', [
            'id' => $id,
            'isactive' => 0,
            'timemodified' => time()
        ]);
    }
    
    /**
     * Delete record along with all children (cascade delete)
     *
     * @param int $id Record ID
     * @return bool Success
     */
    public static function delete_with_children($id) {
        global $DB;
        
        // Get all children recursively
        $children = self::get_all_children($id);
        
        // Delete children (from deepest level first)
        foreach (array_reverse($children) as $child) {
            $DB->update_record('valuemapdoc_markets', [
                'id' => $child->id,
                'isactive' => 0,
                'timemodified' => time()
            ]);
        }
        
        // Delete main record
        $DB->update_record('valuemapdoc_markets', [
            'id' => $id,
            'isactive' => 0,
            'timemodified' => time()
        ]);
        
        return true;
    }
    
    /**
     * Validate hierarchy - check if parent-child relationship is allowed
     *
     * @param string $child_type Child type
     * @param int $parent_id Parent ID
     * @return bool Valid relationship
     */
    public static function validate_hierarchy($child_type, $parent_id) {
        global $DB;
        
        $rules = self::get_hierarchy_rules();
        $required_parent_type = $rules[$child_type];
        
        if ($parent_id == 0) {
            // No parent - only allowed for markets
            return $child_type === self::TYPE_MARKET;
        }
        
        if ($required_parent_type === null) {
            // This type shouldn't have a parent
            return false;
        }
        
        // Check if parent exists and has correct type
        $parent = $DB->get_record('valuemapdoc_markets', ['id' => $parent_id, 'isactive' => 1]);
        if (!$parent) {
            return false;
        }
        
        return $parent->type === $required_parent_type;
    }
    
    /**
 * Check if name already exists for given type and context
 * POPRAWIONE: sprawdza w ramach instancji modułu, a nie kursu
 *
 * @param string $name Name to check
 * @param string $type Record type
 * @param int $valuemapdocid ValueMapDoc instance ID
 * @param int $parentid Parent ID
 * @param int $exclude_id Record ID to exclude from check (for updates)
 * @return bool Name exists
 */
public static function name_exists($name, $type, $cmid, $parentid = 0, $exclude_id = 0) {
    global $DB;
    
    // Użyj sql_compare_text() dla porównania pola TEXT
    $name_comparison = $DB->sql_compare_text('name');
    $name_param = $DB->sql_compare_text(':name');
    
    $sql = "SELECT * FROM {valuemapdoc_markets} 
            WHERE cmid = :cmid 
            AND type = :type 
            AND $name_comparison = $name_param
            AND parentid = :parentid 
            AND isactive = 1";
    
    $params = [
        'cmid' => $cmid,
        'type' => $type,
        'name' => trim($name),
        'parentid' => $parentid
    ];
    
    $existing = $DB->get_record_sql($sql, $params);
    
    if (!$existing) {
        return false;
    }
    
    // If excluding a record (for updates), check if it's not the same record
    if ($exclude_id > 0 && $existing->id == $exclude_id) {
        return false;
    }
    
    return true;
}
    
    /**
     * Check if user can edit this entry
     *
     * @param int $marketid Market ID
     * @param \context $context Context
     * @param int $userid User ID
     * @return bool Can edit
     */
    public static function can_edit($marketid, $context, $userid) {
        global $DB;
        
        $market = $DB->get_record('valuemapdoc_markets', ['id' => $marketid]);
        if (!$market) {
            return false;
        }
        
        // Own entries can always be edited
        if ($market->userid == $userid) {
            return true;
        }
        
        // Check capabilities
        return has_capability('mod/valuemapdoc:manageallmarkets', $context);
    }
    
    /**
 * Get dropdown options for forms
 * POPRAWIONE: używa valuemapdocid
 *
 * @param int $valuemapdocid ValueMapDoc instance ID
 * @param string $type Type filter
 * @param bool $include_empty Include empty option
 * @param int $userid User ID filter
 * @param int $groupid Group ID filter
 * @return array Options array
 */
public static function get_options($valuemapdocid, $type, $include_empty = true, $userid = 0, $groupid = 0) {
    $records = self::get_by_type($valuemapdocid, $type, $userid, $groupid);
    $options = [];
    
    if ($include_empty) {
        $options[0] = get_string('choose');
    }
    
    foreach ($records as $record) {
        $options[$record->id] = $record->name;
    }
    
    return $options;
}
    
/**
 * Count records by type
 * POPRAWIONE: używa valuemapdocid
 *
 * @param int $valuemapdocid ValueMapDoc instance ID
 * @param string $type Type filter
 * @return int Count
 */
public static function count_by_type($valuemapdocid, $type = '') {
    global $DB;
    
    $conditions = [
        'valuemapdocid' => $valuemapdocid,
        'isactive' => 1
    ];
    
    if (!empty($type)) {
        $conditions['type'] = $type;
    }
    
    return $DB->count_records('valuemapdoc_markets', $conditions);
}
    
/**
 * Get statistics for module instance
 * POPRAWIONE: używa valuemapdocid
 *
 * @param int $valuemapdocid ValueMapDoc instance ID
 * @return array Statistics
 */
public static function get_statistics($valuemapdocid) {
    return [
        'markets' => self::count_by_type($valuemapdocid, self::TYPE_MARKET),
        'customers' => self::count_by_type($valuemapdocid, self::TYPE_CUSTOMER),
        'opportunities' => self::count_by_type($valuemapdocid, self::TYPE_OPPORTUNITY),
        'persons' => self::count_by_type($valuemapdocid, self::TYPE_PERSON),
        'total' => self::count_by_type($valuemapdocid)
    ];
}
    
    /**
 * Search records by name
 * POPRAWIONE: używa valuemapdocid
 *
 * @param int $valuemapdocid ValueMapDoc instance ID
 * @param string $query Search query
 * @param string $type Type filter (optional)
 * @return array Matching records
 */
public static function search($valuemapdocid, $query, $type = '') {
    global $DB;
    
    $params = [$valuemapdocid, 1];
    
    $sql = "SELECT * FROM {valuemapdoc_markets} 
            WHERE valuemapdocid = ? AND isactive = ?";
    
    if (!empty($type)) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search_term = '%' . $query . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    
    $sql .= " ORDER BY type ASC, name ASC";
    
    return $DB->get_records_sql($sql, $params);
}
    
    /**
     * Render hierarchical list of markets
     *
     * @param array $markets Array of market records
     * @param int $cmid Course module ID
     * @return string HTML output
     */
    public static function render_hierarchical_list($markets, $cmid) {
        $html = '<div class="markets-hierarchy">';
        
        foreach ($markets as $market) {
            $html .= self::render_market_item($market, $cmid);
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render single market item with children
     *
     * @param \stdClass $market Market record
     * @param int $cmid Course module ID
     * @param int $level Indentation level
     * @return string HTML output
     */
    private static function render_market_item($market, $cmid, $level = 0) {
        global $DB;
        
        $icon = self::get_type_icon($market->type);
        $indent = $level * 25;
        
        $html = '<div class="market-item mb-3" style="margin-left: ' . $indent . 'px; padding: 10px; border-left: 3px solid ' . self::get_type_color($market->type) . '; background-color: #f8f9fa;">';
        
        // Header with name and type
        $html .= '<div class="market-header d-flex justify-content-between align-items-center">';
        $html .= '<div>';
        $html .= '<span style="font-size: 1.2em; margin-right: 8px;">' . $icon . '</span>';
        $html .= '<strong>' . format_string($market->name) . '</strong>';
        $html .= ' <small class="text-muted">(' . ucfirst($market->type) . ')</small>';
        $html .= '</div>';
        
        // Action buttons
        $html .= '<div class="market-actions">';
        
        // Edit button
        $edit_url = new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'edit',
            'recordid' => $market->id
        ]);
        $html .= \html_writer::link($edit_url, 'Edit', ['class' => 'btn btn-sm btn-outline-primary me-1']);
        
        // Delete button
        $delete_url = new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'delete',
            'recordid' => $market->id,
            'sesskey' => sesskey()
        ]);
        $html .= \html_writer::link($delete_url, 'Delete', [
            'class' => 'btn btn-sm btn-outline-danger me-2',
            'onclick' => 'return confirm("Delete this ' . $market->type . '? This will also delete all related records.");'
        ]);
        
        // Add children buttons based on type
        $add_buttons = self::get_add_children_buttons($market, $cmid);
        $html .= $add_buttons;
        
        $html .= '</div>';
        $html .= '</div>';
        
        // Description
        if (!empty($market->description)) {
            $html .= '<div class="market-description mt-2 text-muted" style="font-size: 0.9em;">';
            $html .= format_text($market->description);
            $html .= '</div>';
        }

        // DODAJ DODATKOWE POLA
        $html .= self::render_additional_fields($market);

        
        // Children
        $children = $DB->get_records('valuemapdoc_markets', 
                                    ['parentid' => $market->id, 'isactive' => 1], 
                                    'type ASC, name ASC');
        
        if (!empty($children)) {
            $html .= '<div class="market-children mt-2">';
            foreach ($children as $child) {
                $html .= self::render_market_item($child, $cmid, $level + 1);
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }


    /**
 * Render additional fields in market item
 */
private static function render_additional_fields($market) {
    $html = '';
    
    $fields = ['baseinfo', 'outsideinfo', 'insideinfo'];
    $has_content = false;
    
    foreach ($fields as $field) {
        if (!empty($market->$field)) {
            $has_content = true;
            break;
        }
    }
    
    if (!$has_content) {
        return '';
    }
    
    $html .= '<div class="market-additional-fields mt-2">';
    
    foreach ($fields as $field) {
        if (!empty($market->$field)) {
            $label = self::get_field_label($field, $market->type);
            $html .= '<div class="additional-field mb-1">';
            $html .= '<strong>' . $label . ':</strong> ';
            $html .= '<span class="text-muted">' . format_text($market->$field, FORMAT_PLAIN) . '</span>';
            $html .= '</div>';
        }
    }
    
    $html .= '</div>';
    
    return $html;
}

    
    /**
     * Get add children buttons for a market item
     *
     * @param \stdClass $market Market record
     * @param int $cmid Course module ID
     * @return string HTML buttons
     */
    private static function get_add_children_buttons($market, $cmid) {
        $html = '';
        $allowed_children = self::get_allowed_children($market->type);
        
        foreach ($allowed_children as $child_type) {
            $add_url = new \moodle_url('/mod/valuemapdoc/markets.php', [
                'id' => $cmid,
                'action' => 'add',
                'type' => $child_type,
                'parentid' => $market->id
            ]);
            
            $button_text = 'Add ' . ucfirst($child_type);
            $button_class = 'btn btn-sm ' . self::get_type_button_class($child_type);
            
            $html .= \html_writer::link($add_url, $button_text, ['class' => $button_class . ' me-1']);
        }
        
        return $html;
    }
    
    /**
     * Get icon for record type
     *
     * @param string $type Record type
     * @return string Icon emoji
     */
    private static function get_type_icon($type) {
        switch ($type) {
            case self::TYPE_MARKET:
                return '🔍'; //'💎' '🎁'
            case self::TYPE_CUSTOMER:
                return '🏛';
            case self::TYPE_PERSON:
                return '👤';
            case self::TYPE_OPPORTUNITY:
                return '💰' ;//'🎯''💵' '💣'
            default:
                return '📄';
        }
    }
    
    /**
     * Get color for record type
     *
     * @param string $type Record type
     * @return string Color hex code
     */
    private static function get_type_color($type) {
        switch ($type) {
            case self::TYPE_MARKET:
                return '#007bff'; // Blue
            case self::TYPE_CUSTOMER:
                return '#28a745'; // Green
            case self::TYPE_PERSON:
                return '#17a2b8'; // Teal
            case self::TYPE_OPPORTUNITY:
                return '#ffc107'; // Yellow
            default:
                return '#6c757d'; // Gray
        }
    }
    
    /**
     * Get button class for record type
     *
     * @param string $type Record type
     * @return string Bootstrap button class
     */
    private static function get_type_button_class($type) {
        switch ($type) {
            case self::TYPE_MARKET:
                return 'btn-primary';
            case self::TYPE_CUSTOMER:
                return 'btn-success';
            case self::TYPE_PERSON:
                return 'btn-info';
            case self::TYPE_OPPORTUNITY:
                return 'btn-warning';
            default:
                return 'btn-secondary';
        }
    }
    
 /**
 * Export markets data to array (for JSON/CSV export)
 * POPRAWIONE: używa valuemapdocid
 *
 * @param int $valuemapdocid ValueMapDoc instance ID
 * @return array Export data
 */
public static function export_data($valuemapdocid) {
    $markets = self::get_by_type($valuemapdocid);
    $export_data = [];
    
    foreach ($markets as $market) {
        $breadcrumb = self::get_breadcrumb($market->id);
        $path = array_map(function($item) { return $item->name; }, $breadcrumb);
        
        $export_data[] = [
            'id' => $market->id,
            'type' => $market->type,
            'name' => $market->name,
            'description' => $market->description,
            'path' => implode(' > ', $path),
            'parent_id' => $market->parentid,
            'created' => date('Y-m-d H:i:s', $market->timecreated),
            'modified' => date('Y-m-d H:i:s', $market->timemodified)
        ];
    }
    
    return $export_data;
}

    /**
    * Get field labels based on type
    *
    * @param string $type Record type
    * @return array Field labels
    */
    public static function get_field_labels($type) {
        switch ($type) {
            case self::TYPE_MARKET:
                return [
                    'baseinfo' => 'Market',
                    'baseinfo_desc' => 'Primary market characteristics',
                    'outsideinfo' => 'Market Trends (Outside)',
                    'outsideinfo_desc' => 'External market trends and influences',
                    'insideinfo' => 'Current / Bad Approach (Inside)',
                    'insideinfo_desc' => 'Their current approach market not supporting out offer'
            ];
            
        case self::TYPE_CUSTOMER:
            return [
                'baseinfo' => 'Industry',
                'baseinfo_desc' => 'Customer industry or sector (from their perspective)',
                'outsideinfo' => 'External Triggers (Outside)',
                'outsideinfo_desc' => 'External factors and triggers driving customer needs',
                'insideinfo' => 'Internal Initiatives or projects (Inside)',
                'insideinfo_desc' => 'Customer internal initiatives and projects'
            ];
            
        case self::TYPE_PERSON:
            return [
                'baseinfo' => 'Role',
                'baseinfo_desc' => 'Person role, position or responsibilities',
                'outsideinfo' => 'Opportunities',
                'outsideinfo_desc' => 'Opportunities this person sees',
                'insideinfo' => 'Risks',
                'insideinfo_desc' => 'Risks and challenges they face'
            ];
            
        case self::TYPE_OPPORTUNITY:
            return [
                'baseinfo' => 'Opportunity Type',
                'baseinfo_desc' => 'Type or product category of this opportunity',
                'outsideinfo' => 'Market Context (Outside)',
                'outsideinfo_desc' => 'External market context and environment',
                'insideinfo' => 'Internal Readiness (Inside)',
                'insideinfo_desc' => 'Our internal readiness and preparation'
            ];
            
        default:
            return [
                'baseinfo' => 'Additional Info 1',
                'baseinfo_desc' => 'Additional information field 1',
                'outsideinfo' => 'Additional Info 2',
                'outsideinfo_desc' => 'Additional information field 2',
                'insideinfo' => 'Additional Info 3',
                'insideinfo_desc' => 'Additional information field 3'
            ];
    }
}

/**
 * Get field label for specific field and type
 *
 * @param string $field Field name (baseinfo, outsideinfo, insideinfo)
 * @param string $type Record type
 * @return string Field label
 */
public static function get_field_label($field, $type) {
    $labels = self::get_field_labels($type);
    return $labels[$field] ?? ucfirst($field);
}



/**
 * Render hierarchical list using Mustache templates
 *
 * @param array $markets Array of market records
 * @param int $cmid Course module ID
 * @param bool $readonly Whether to show in readonly mode
 * @return array Template data for Mustache
 */
public static function get_hierarchy_template_data($markets,  $cmid, $readonly = false) {
    global $OUTPUT;
    
    $template_data = [
        'readonly' => $readonly,
        'add_market_url' => new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'add',
            'type' => 'market'
        ]),
        'has_markets' => !empty($markets),
        'markets' => []
    ];
    
    foreach ($markets as $market) {
        $template_data['markets'][] = self::get_market_item_data($market, $cmid, 0, $readonly);
    }
    
    return $template_data;
}

/**
 * Get template data for single market item
 *
 * @param \stdClass $market Market record
 * @param int $cmid Course module ID
 * @param int $level Indentation level
 * @param bool $readonly Readonly mode
 * @return array Template data
 
private static function get_market_item_data($market, $cmid, $level = 0, $readonly = false) {
    global $DB;
    
    $children = $DB->get_records('valuemapdoc_markets', 
                                ['parentid' => $market->id, 'isactive' => 1], 
                                'type ASC, name ASC');
    
    $children_data = [];
    foreach ($children as $child) {
        $children_data[] = self::get_market_item_data($child,  $cmid, $level + 1, $readonly);
    }
    
    $additional_fields = self::get_additional_fields_data($market);
    
    return [
        'id' => $market->id,
        'name' => format_string($market->name),
        'type' => $market->type,
        'type_display' => ucfirst($market->type),
        'icon' => self::get_type_icon($market->type),
        'color' => self::get_type_color($market->type),
        'indent' => $level * 25,
        'readonly' => $readonly,
        
        // Description
        'has_description' => !empty($market->description),
        'description' => format_text($market->description),
        
        // Additional fields
        'has_additional_fields' => !empty($additional_fields),
        'additional_fields' => $additional_fields,
        
        // Actions (if not readonly)
        'edit_url' => new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'edit',
            'recordid' => $market->id
        ]),
        'delete_url' => new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'delete',
            'recordid' => $market->id,
            'sesskey' => sesskey()
        ]),
        'detail_url' => new \moodle_url('/mod/valuemapdoc/view_market.php', [
            'id' => $cmid,
            'marketid' => $market->id,
        ]),
        'add_children_buttons' => self::get_add_children_buttons_data($market, $cmid, $readonly),
        
        // Children
        'has_children' => !empty($children_data),
        'children' => $children_data
    ];
}
    */

/**
 * Get additional fields data for template
 */
private static function get_additional_fields_data($market) {
    $fields = [];
    
    $field_configs = [
        'baseinfo' => self::get_field_label('baseinfo', $market->type),
        'outsideinfo' => self::get_field_label('outsideinfo', $market->type),
        'insideinfo' => self::get_field_label('insideinfo', $market->type)
    ];
    
    foreach ($field_configs as $field => $label) {
        if (!empty($market->$field)) {
            $fields[] = [
                'label' => $label,
                'value' => format_text($market->$field, FORMAT_PLAIN),
                'has_value' => true
            ];
        }
    }
    
    return $fields;
}

/** Get allowed child types based on parent type
 */
private static function get_add_children_buttons_data($market, $cmid, $readonly = false) {
    if ($readonly) {
        return ['buttons' => []];
    }
    
    $allowed_children = self::get_allowed_children($market->type);
    $buttons = [];
    
    if (!empty($allowed_children)) {
        foreach ($allowed_children as $child_type) {
            $add_url = new \moodle_url('/mod/valuemapdoc/markets.php', [
                'id' => $cmid,
                'action' => 'add',
                'type' => $child_type,
                'parentid' => $market->id
            ]);
            
            $buttons[] = [
                'url' => $add_url,
                'text' => 'Add ' . ucfirst($child_type),
                'title' => 'Add ' . ucfirst($child_type) . ' to ' . format_string($market->name),
                'class' => self::get_type_button_class($child_type),
                'icon' => self::get_type_icon($child_type),
                'single_button' => count($allowed_children) === 1,  // Flaga dla pojedynczego przycisku
            ];
        }
    }
    
    return [
        'buttons' => $buttons,
        'has_buttons' => !empty($buttons),
        'single_button' => count($buttons) === 1,
        'multiple_buttons' => count($buttons) > 1
    ];
}

/**
 * Get button icon for type
 */
private static function get_type_button_icon($type) {
    switch ($type) {
        case self::TYPE_CUSTOMER:
            return 'fa-user';
        case self::TYPE_PERSON:
            return 'fa-user-circle';
        case self::TYPE_OPPORTUNITY:
            return 'fa-bullseye';
        default:
            return 'fa-plus';
    }
}

/**
 * Render hierarchy using Mustache
 */
public static function render_hierarchy_mustache($markets, $cmid, $readonly = false) {
    global $OUTPUT;
    
    $template_data = self::get_hierarchy_template_data($markets, $cmid, $readonly);
    return $OUTPUT->render_from_template('mod_valuemapdoc/markets_hierarchy', $template_data);
}

/**
 * Get compact hierarchy template data
 */
public static function get_compact_hierarchy_template_data($markets, $cmid, $readonly = false) {
    $template_data = [
        'title' => 'Markets & Customers',
        'readonly' => $readonly,
        'add_market_url' => new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'add',
            'type' => 'market'
        ]),
        'has_markets' => !empty($markets),
        'markets' => []
    ];
    
    foreach ($markets as $market) {
        $template_data['markets'][] = self::get_market_compact_item_data($market, $cmid, 0, $readonly);
    }
    
    return $template_data;
}

/**
 * Get compact item data for market
 */
public static function get_market_compact_item_data($market, $cmid, $level = 0, $readonly = false) {
    global $DB;
    
    $children = $DB->get_records('valuemapdoc_markets', 
                                ['parentid' => $market->id, 'isactive' => 1], 
                                'type ASC, name ASC');
    
    $children_data = [];
    foreach ($children as $child) {
        $children_data[] = self::get_market_compact_item_data($child, $cmid, $level + 1, $readonly);
    }
    
    // Check if has additional info
    $has_additional_info = !empty($market->baseinfo) || !empty($market->outsideinfo) || !empty($market->insideinfo);
    
    // Short description (first 100 chars)
    $short_description = '';
    if (!empty($market->description)) {
        $short_description = strlen($market->description) > 100 ? 
            substr(strip_tags($market->description), 0, 97) . '...' : 
            strip_tags($market->description);
    }
    
    return [
        'id' => $market->id,
        'name' => format_string($market->name),
        'type' => $market->type,
        'type_display' => ucfirst($market->type),
        'icon' => self::get_type_icon($market->type),
        'color' => self::get_type_color($market->type),
        'indent' => $level * 20,
        'readonly' => $readonly,
        
        // URLs
        'detail_url' => (new \moodle_url('/mod/valuemapdoc/view_market.php', [
            'id' => $cmid,
            'marketid' => $market->id
        ])),//->out_omit_querystring(),
        'edit_url' => (new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'edit',
            'recordid' => $market->id
        ])),//->out_omit_querystring(),
        'delete_url' => (new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'delete',
            'recordid' => $market->id,
            'sesskey' => sesskey()
        ])),//->out_omit_querystring(),
        
        // Compact info
        'short_description' => $short_description,
        'has_additional_info' => $has_additional_info,
        'children_count' => count($children),
        
        // Actions
        'add_children_buttons' => self::get_add_children_buttons_data($market, $cmid, $readonly),
        
        // Children
        'has_children' => !empty($children_data),
        'children' => $children_data
    ];
}

/**
 * Get detailed market template data
 */
 public static function get_market_detail_template_data($market, $cmid) {
    global $DB;
    
    // Parse dynamic fields first
    $market = self::parse_dynamic_fields($market);
    
    // Get breadcrumb
    $breadcrumb = self::get_breadcrumb($market->id);
    $breadcrumb_data = [];
    foreach ($breadcrumb as $item) {
        $breadcrumb_data[] = [
            'name' => $item->name,
            'detail_url' => new \moodle_url('/mod/valuemapdoc/view_market.php', [
                'id' => $cmid,
                'marketid' => $item->id
            ]),
            'is_current' => ($item->id == $market->id)
        ];
    }
    
    // Get children
    $children = $DB->get_records('valuemapdoc_markets', 
                                ['parentid' => $market->id, 'isactive' => 1], 
                                'type ASC, name ASC');
    
    $children_data = [];
    foreach ($children as $child) {
        $short_desc = !empty($child->description) ? 
            substr(strip_tags($child->description), 0, 100) . '...' : '';
            
        $children_data[] = [
            'id' => $child->id,
            'name' => format_string($child->name),
            'type' => $child->type,
            'type_display' => ucfirst($child->type),
            'type_color' => self::get_type_bootstrap_color($child->type),
            'icon' => self::get_type_icon($child->type),
            'short_description' => $short_desc,
            'detail_url' => new \moodle_url('/mod/valuemapdoc/view_market.php', [
                'id' => $cmid,
                'marketid' => $child->id
            ]),
            'edit_url' => new \moodle_url('/mod/valuemapdoc/markets.php', [
                'id' => $cmid,
                'action' => 'edit',
                'recordid' => $child->id
            ])
        ];
    }
    
    // Statistics
    $stats = [];
    foreach (['customer', 'person', 'opportunity'] as $child_type) {
        $count = $DB->count_records('valuemapdoc_markets', [
            'parentid' => $market->id,
            'type' => $child_type,
            'isactive' => 1
        ]);
        if ($count > 0) {
            $stats[] = [
                'label' => ucfirst($child_type) . 's',
                'count' => $count
            ];
        }
    }
    
    // Get dynamic fields template data (replaces old additional_fields)
    $dynamic_fields_data = self::get_dynamic_fields_template_data($market);
    
    return [
        'id' => $market->id,
        'name' => format_string($market->name),
        'type' => $market->type,
        'type_display' => ucfirst($market->type),
        'type_color' => self::get_type_bootstrap_color($market->type),
        'icon' => self::get_type_icon($market->type),
        
        // Description
        'has_description' => !empty($market->description),
        'description' => format_text($market->description),
        
        // URLs
        'back_url' => new \moodle_url('/mod/valuemapdoc/view.php', ['id' => $cmid], 'markets-tab'),
        'edit_url' => new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'edit',
            'recordid' => $market->id
        ]),
        
        // Breadcrumb
        'breadcrumb_path' => count($breadcrumb_data) > 1,
        'breadcrumb' => $breadcrumb_data,
        
        // Dynamic fields (new approach instead of old additional_fields)
        'has_additional_fields' => $dynamic_fields_data['has_dynamic_fields'],
        'additional_fields' => $dynamic_fields_data['dynamic_fields'],
                
        // Children
        'has_children' => !empty($children_data),
        'children' => $children_data,
        
        // Statistics
        'has_statistics' => !empty($stats),
        'statistics' => $stats,
        
        // Actions
        'add_children_buttons' => self::get_add_children_buttons_data($market, $cmid, false),
        
        // Metadata
        'created_date' => date('Y-m-d H:i', $market->timecreated)
    ];
}

/**
 * Get Bootstrap color class for type
 */
private static function get_type_bootstrap_color($type) {
    switch ($type) {
        case self::TYPE_MARKET: return 'primary';
        case self::TYPE_CUSTOMER: return 'success';
        case self::TYPE_PERSON: return 'info';
        case self::TYPE_OPPORTUNITY: return 'warning';
        default: return 'secondary';
    }
}

/**
 * Render compact hierarchy
 */
public static function render_compact_hierarchy($markets, $cmid, $readonly = false) {
    global $OUTPUT;
    
    $template_data = self::get_compact_hierarchy_template_data($markets, $cmid, $readonly);
    return $OUTPUT->render_from_template('mod_valuemapdoc/markets_hierarchy_compact', $template_data);
}


/**
 * Get template data for dynamic fields
 *
 * @param \stdClass $record Record with parsed fields
 * @return array Template data for dynamic fields
 */
public static function get_dynamic_fields_template_data($record) {
    $fields_config = self::get_fields_for_type($record->type);
    $fields_data = [];
    
    if (empty($fields_config)) {
        return ['has_dynamic_fields' => false, 'dynamic_fields' => []];
    }

    // Sort fields by order
    uasort($fields_config, function($a, $b) {
        return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
    });

    foreach ($fields_config as $field_name => $field_config) {
        $value = self::get_field_value($record, $field_name);
        
        if (!empty(trim($value))) {
            $field_data = [
                'label' => $field_config['label'],
                'value' => $value,
                'formatted_value' => self::format_field_value($value, $field_config['type']),
                'type' => $field_config['type'],
                'is_textarea' => ($field_config['type'] === 'textarea'),
                'is_text' => ($field_config['type'] === 'text'),
                'is_select' => ($field_config['type'] === 'select'),
                'has_value' => true
            ];
            
            $fields_data[] = $field_data;
        }
    }
    
    return [
        'has_dynamic_fields' => !empty($fields_data),
        'dynamic_fields' => $fields_data
    ];
}

/**
 * Format field value for display
 *
 * @param string $value Field value
 * @param string $type Field type
 * @return string Formatted value
 */
private static function format_field_value($value, $type) {
    switch ($type) {
        case 'textarea':
            return nl2br(htmlspecialchars($value));
        case 'text':
        case 'select':
        default:
            return htmlspecialchars($value);
    }
}

/**
 * Get updated template data for market item with dynamic fields
 */
private static function get_market_item_data($market, $cmid, $level = 0, $readonly = false) {
    global $DB;
    
    // Parse dynamic fields
    $market = self::parse_dynamic_fields($market);
    
    $children = $DB->get_records('valuemapdoc_markets', 
                                ['parentid' => $market->id, 'isactive' => 1], 
                                'type ASC, name ASC');
    
    $children_data = [];
    foreach ($children as $child) {
        $children_data[] = self::get_market_item_data($child,  $cmid, $level + 1, $readonly);
    }
    
    // Get dynamic fields template data
    $dynamic_fields_data = self::get_dynamic_fields_template_data($market);
    
    return [
        'id' => $market->id,
        'name' => format_string($market->name),
        'type' => $market->type,
        'type_display' => ucfirst($market->type),
        'icon' => self::get_type_icon($market->type),
        'color' => self::get_type_color($market->type),
        'indent' => $level * 25,
        'readonly' => $readonly,
        
        // Description
        'has_description' => !empty($market->description),
        'description' => format_text($market->description),
        
        // Dynamic fields (replaces old additional_fields)
        'has_dynamic_fields' => $dynamic_fields_data['has_dynamic_fields'],
        'dynamic_fields' => $dynamic_fields_data['dynamic_fields'],
        
        // Actions (if not readonly)
        'edit_url' => new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'edit',
            'recordid' => $market->id
        ]),
        'delete_url' => new \moodle_url('/mod/valuemapdoc/markets.php', [
            'id' => $cmid,
            'action' => 'delete',
            'recordid' => $market->id,
            'sesskey' => sesskey()
        ]),
        'detail_url' => new \moodle_url('/mod/valuemapdoc/view_market.php', [
            'id' => $cmid,
            'marketid' => $market->id,
        ]),
        'add_children_buttons' => self::get_add_children_buttons_data($market, $cmid, $readonly),
        
        // Children
        'has_children' => !empty($children_data),
        'children' => $children_data
    ];
}


/**
 * Get export data for AI with dynamic fields
 * POPRAWIONE: używa valuemapdocid
 *
 * @param int $valuemapdocid ValueMapDoc instance ID
 * @return array Complete data structure for AI
 */
public static function export_for_ai($valuemapdocid) {
    $market = self::get_by_id($valuemapdocid);
    $export_data = [];
    
//    foreach ($markets as $market) {
        // Parse dynamic fields
        $market = self::parse_dynamic_fields($market);
        
        $market_data = [
            'id' => $market->id,
            'type' => $market->type,
            'name' => $market->name,
            'description' => $market->description,
            'dynamic_fields' => [],
//            'children' => []
        ];
        
        // Add dynamic fields with labels
        $fields_config = self::get_fields_for_type($market->type);
        foreach ($fields_config as $field_name => $field_config) {
            $value = self::get_field_value($market, $field_name);
            if (!empty(trim($value))) {
                $market_data['dynamic_fields'][] = [
                    'field_name' => $field_name,
                    'label' => $field_config['label'],
                    'value' => $value,
                    'type' => $field_config['type']
                ];
            }
        }
        
        /*
        // Add children recursively
        $children = self::get_children($market->id);
        foreach ($children as $child) {
            $child = self::parse_dynamic_fields($child);
            
            $child_data = [
                'id' => $child->id,
                'type' => $child->type,
                'name' => $child->name,
                'description' => $child->description,
                'dynamic_fields' => []
            ];
            
            // Add child dynamic fields
            $child_fields_config = self::get_fields_for_type($child->type);
            foreach ($child_fields_config as $field_name => $field_config) {
                $value = self::get_field_value($child, $field_name);
                if (!empty(trim($value))) {
                    $child_data['dynamic_fields'][] = [
                        'field_name' => $field_name,
                        'label' => $field_config['label'],
                        'value' => $value,
                        'type' => $field_config['type']
                    ];
                }
            }
            
            $market_data['children'][] = $child_data;
            
        }
            */
        
//        $export_data[] = $market_data;
//    }
    
    return $market_data; //$export_data;
}

// Funkcja pomocnicza do wyszukiwania wartości w dynamic_fields
  private static function find_dynamic_field_value($data, $field_name) {
        if (!isset($data['dynamic_fields']) || !is_array($data['dynamic_fields'])) {
            return '';
        }
        
        foreach ($data['dynamic_fields'] as $field) {
            if (isset($field['field_name']) && $field['field_name'] === $field_name) {
                return isset($field['value']) ? $field['value'] : '';
            }
        }
        return '';
    }



/**
 * Get configuration summary for admin
 *
 * @return array Configuration summary
 */
public static function get_configuration_summary() {
    $config = self::get_field_configuration();
    $summary = [];
    
    foreach ($config as $type => $fields) {
        $field_count = count($fields);
        $required_count = count(array_filter($fields, function($field) {
            return $field['required'] ?? false;
        }));
        
        $summary[$type] = [
            'type_display' => ucfirst($type),
            'total_fields' => $field_count,
            'required_fields' => $required_count,
            'field_names' => array_keys($fields)
        ];
    }
    
    return $summary;
}

/**
 * Search in dynamic fields
 * POPRAWIONE: używa valuemapdocid
 *
 * @param int $valuemapdocid ValueMapDoc instance ID
 * @param string $query Search query
 * @param string $type Type filter (optional)
 * @return array Matching records
 */
public static function search_dynamic_fields($valuemapdocid, $query, $type = '') {
    global $DB;
    
    $params = [$valuemapdocid, 1];
    
    $sql = "SELECT * FROM {valuemapdoc_markets} 
            WHERE valuemapdocid = ? AND isactive = ?";
    
    if (!empty($type)) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    // Search in name, description and dynamic_fields JSON
    $search_term = '%' . $query . '%';
    $sql .= " AND (name LIKE ? OR description LIKE ? OR dynamic_fields LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    
    $sql .= " ORDER BY type ASC, name ASC";
    
    $records = $DB->get_records_sql($sql, $params);
    
    // Parse dynamic fields for all records
    foreach ($records as &$record) {
        $record = self::parse_dynamic_fields($record);
    }
    
    return $records;
}
/*
public static function get_filename($marketid, $customerid, $personid, $opportunityid, $prefix) {
    $market = self::get_by_id($marketid);
    $m = isset($market['name']) ? $market['name'] : '';
    $c = isset($customer['name']) ? $customer['name'] : '';
    $p = isset($person['name']) ? $person['name'] : '';
    $o = isset($opportunity['name']) ? $opportunity['name'] : '';
    $f = isset($prefix) ? $prefix : date('Ymd_His') ;

    return preg_replace('/[^a-zA-Z0-9_\-]/', '_', 
        $f . '_' . $m . '_' . $c . '_' . $p . '_' . $o);

    }
*/
//
/**
     * Get filename based on template
     * Enhanced version using new placeholder system
     * 
     * @param int $marketid Market ID
     * @param int $customerid Customer ID  
     * @param int $personid Person ID
     * @param int $opportunityid Opportunity ID
     * @param string $prefix Template prefix
     * @return string Generated filename
     */
    public static function get_filename($marketid, $customerid, $personid, $opportunityid, $prefix = null, $template = null) {
/*        if ($template === null) {
            $template = '{market.name}.{customer.name}.{person.name}.{opportunity.name}' ..'';
        }
*/        
         return self::generate_filename_from_template($marketid, $customerid, $personid, $opportunityid, $prefix, $template);
          
    }
// - dynamic replacement



    /**
     * Advanced placeholder replacement using markets class structure
     * 
     * @param string $text Tekst zawierający placeholdery do podmiany
     * @param \stdClass|array|null $market Dane rynku (obiekt z bazy lub tablica)
     * @param \stdClass|array|null $customer Dane klienta
     * @param \stdClass|array|null $person Dane osoby
     * @param \stdClass|array|null $opportunity Dane możliwości
     * @return string Tekst z podmienionymi wartościami
     */
    public static function replace_placeholders_advanced($text, $market = null, $customer = null, $person = null, $opportunity = null) {

        
        
        // Mapowanie typów na dane
        $data_map = [
            'market' => $market,
            'customer' => $customer, 
            'person' => $person,
            'opportunity' => $opportunity
        ];
        
        $result = $text;
        
        // KROK 1: Obsługa placeholderów z fallback: {type.field || "default"}
//        $fallback_pattern = '/\{([^}]+?)\s*\|\|\s*(["\'])(.*?)\2\}/';
        $fallback_pattern = '/\[([^\]]+?)\s*\|\|\s*(["\'])(.*?)\2\]/';
        
        $result = preg_replace_callback($fallback_pattern, function($matches) use ($data_map) {
            $placeholder_content = trim($matches[1]);
            $default_value = $matches[3];
            die("fallback_pattern: " . $placeholder_content . " || " . $default_value);
            
            $value = self::resolve_placeholder($placeholder_content, $data_map);
            
            // Jeśli wartość jest pusta, użyj domyślnej
            return (!empty($value)) ? $value : $default_value;
        }, $result);
        
        // KROK 2: Obsługa zwykłych placeholderów: {type.field}
//        $simple_pattern = '/\{([^}]+?)\}/';
        $simple_pattern = '/\[([^\]]+?)\]/';
        
        $result = preg_replace_callback($simple_pattern, function($matches) use ($data_map) {
            $placeholder_content = trim($matches[1]);
            
            // Sprawdź czy to nie jest placeholder, który już został przetworzony
            if (strpos($placeholder_content, '||') !== false) {
                return $matches[0]; // Zostaw niezmienione
            }
            
            $value = self::resolve_placeholder($placeholder_content, $data_map);

            return $value;
        }, $result);
        
        return $result;
    }

    /**
     * Funkcja do resolwowania placeholdera
     * 
     * @param string $placeholder_content Zawartość placeholdera (np. "market.name")
     * @param array $data_map Mapa danych
     * @return string Znaleziona wartość lub pusty string
     */
    private static function resolve_placeholder($placeholder_content, $data_map) {
        // Parsuj placeholder: type.field_name
        $parts = explode('.', $placeholder_content, 2);
        if (count($parts) !== 2) {
            return ''; // Nieprawidłowy format
        }
        
        $type = trim($parts[0]);
        $field_name = trim($parts[1]);
        
        // Sprawdź czy mamy dane dla tego typu
        if (!isset($data_map[$type]) || empty($data_map[$type])) {
            return '';
        }
        
        return self::get_value_from_data($data_map[$type], $field_name);
    }

    /**
     * Funkcja pomocnicza do pobierania wartości z obiektu/tablicy
     * 
     * @param mixed $data Dane (obiekt lub tablica)
     * @param string $field_name Nazwa pola
     * @return string Wartość pola
     */
    private static function get_value_from_data($data, $field_name) {
        if (empty($data)) {
            return '';
        }
        
        // Jeśli to obiekt z bazy danych (po parse_dynamic_fields)
        if (is_object($data)) {
            // Sprawdź podstawowe pola
            if (property_exists($data, $field_name)) {
                return $data->$field_name;
            }
            
            // Sprawdź pola dynamiczne z prefiksem field_
            $dynamic_field = 'field_' . $field_name;
            if (property_exists($data, $dynamic_field)) {
                return $data->$dynamic_field;
            }
            
            // Jeśli ma nieparsowane dynamic_fields JSON
            if (property_exists($data, 'dynamic_fields') && !empty($data->dynamic_fields)) {
                $dynamic_fields = is_string($data->dynamic_fields) ? 
                    json_decode($data->dynamic_fields, true) : $data->dynamic_fields;
                
                if (is_array($dynamic_fields) && isset($dynamic_fields[$field_name])) {
                    return $dynamic_fields[$field_name];
                }
            }
        }
        
        // Jeśli to tablica (np. z export_for_ai)
        if (is_array($data)) {
            // Sprawdź podstawowe pola
            if (isset($data[$field_name])) {
                return $data[$field_name];
            }
            
            // Sprawdź w dynamic_fields array
            if (isset($data['dynamic_fields']) && is_array($data['dynamic_fields'])) {
                foreach ($data['dynamic_fields'] as $field) {
                    if (isset($field['field_name']) && $field['field_name'] === $field_name) {
                        return isset($field['value']) ? $field['value'] : '';
                    }
                }
            }
        }
        
        return '';
    }

    /**
     * Wersja kompatybilna z klasą markets - pobiera dane z bazy
     * 
     * @param string $text Tekst do przetworzenia
     * @param int $market_id ID rynku
     * @param int $customer_id ID klienta
     * @param int $person_id ID osoby  
     * @param int $opportunity_id ID możliwości
     * @return string Przetworzony tekst
     */
    public static function replace_placeholders_from_db($text, $market_id = 0, $customer_id = 0, $person_id = 0, $opportunity_id = 0) {
        
        // Pobierz dane z bazy używając metod klasy markets
        $market = $market_id > 0 ? self::get_by_id($market_id) : null;
        $customer = $customer_id > 0 ? self::get_by_id($customer_id) : null;
        $person = $person_id > 0 ? self::get_by_id($person_id) : null;
        $opportunity = $opportunity_id > 0 ? self::get_by_id($opportunity_id) : null;

        
        return self::replace_placeholders_advanced($text, $market, $customer, $person, $opportunity);
    }

    /**
     * Wersja wykorzystująca dane w formacie export
     * 
     * @param string $text Tekst do przetworzenia
     * @param array $market_data Dane rynku w formacie export_for_ai
     * @param array $customer_data Dane klienta w formacie export_for_ai
     * @param array $person_data Dane osoby w formacie export_for_ai
     * @param array $opportunity_data Dane możliwości w formacie export_for_ai
     * @return string Przetworzony tekst
     */
    public static function replace_placeholders_from_export($text, $market_data = null, $customer_data = null, $person_data = null, $opportunity_data = null) {
        return self::replace_placeholders_advanced($text, $market_data, $customer_data, $person_data, $opportunity_data);
    }

    /**
     * Generowanie nazwy pliku z użyciem szablonu
     * 
     * @param int $market_id
     * @param int $customer_id  
     * @param int $person_id
     * @param int $opportunity_id
     * @param string $template Szablon nazwy pliku
     * @return string Wygenerowana nazwa pliku
     */
    public static function generate_filename_from_template($market_id, $customer_id, $person_id, $opportunity_id, $prefix, $template = null) {
        
    // Mapa ID do odpowiadających im placeholderów
    $id_mapping = [
        'market' => $market_id,
        'customer' => $customer_id,
        'person' => $person_id,
        'opportunity' => $opportunity_id
    ];
    
    if ($template === null) {
        // Buduj domyślny template tylko z dostępnymi elementami
        $available_parts = [];
        foreach ($id_mapping as $key => $id) {
            if ($id > 0) {
                $available_parts[] = "[$key.name]";
            }
        }
        $available_parts[] = $prefix . '_{timestamp}';
        $template = implode('.', $available_parts);
    } else {
        // Usuń placeholdery dla ID = 0 z podanego template
        foreach ($id_mapping as $key => $id) {
            if ($id == 0) {
                // Usuń placeholder wraz z ewentualną kropką przed lub po
                $template = preg_replace('/\.?\[' . $key . '\.[^\]]+\]\.?/', '.', $template);
            }
        }
        
        // Oczyść wielokrotne kropki i kropki na początku/końcu
        $template = preg_replace('/\.{2,}/', '.', $template);
        $template = trim($template, '.');
    }

        
        // Dodaj timestamp jako specjalny placeholder
        $timestamp = date('Ymd_His');
        $template = str_replace('{timestamp}', $timestamp, $template);
        
        $filename = self::replace_placeholders_from_db($template, $market_id, $customer_id, $person_id, $opportunity_id);
        
        // Oczyść nazwę pliku ze znaków specjalnych
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        
    }

    /**
     * Walidacja szablonu - sprawdza czy wszystkie placeholdery są prawidłowe
     * 
     * @param string $template Szablon do sprawdzenia
     * @return array Tablica z błędami (pusta jeśli wszystko OK)
     */
    public static function validate_template($template) {
        $errors = [];
        
        // Znajdź wszystkie placeholdery
        preg_match_all('/\{([^}]+)\}/', $template, $matches);
        
        $valid_types = ['market', 'customer', 'person', 'opportunity'];
        $field_configs = self::get_field_configuration();
        
        foreach ($matches[1] as $placeholder_content) {
            // Pomiń specjalne placeholdery
            if (trim($placeholder_content) === 'timestamp') {
                continue;
            }
            
            // Usuń fallback jeśli istnieje
            $clean_placeholder = preg_replace('/\s*\|\|.*$/', '', trim($placeholder_content));
            
            // Sprawdź format type.field
            $parts = explode('.', $clean_placeholder, 2);
            if (count($parts) !== 2) {
                $errors[] = "Invalid placeholder format: {" . $placeholder_content . "}";
                continue;
            }
            
            $type = trim($parts[0]);
            $field_name = trim($parts[1]);
            
            // Sprawdź czy typ jest prawidłowy
            if (!in_array($type, $valid_types)) {
                $errors[] = "Invalid type '{$type}' in placeholder: {" . $placeholder_content . "}";
                continue;
            }
            
            // Sprawdź czy pole istnieje dla danego typu
            $valid_fields = ['name', 'description']; // Podstawowe pola
            if (isset($field_configs[$type])) {
                $valid_fields = array_merge($valid_fields, array_keys($field_configs[$type]));
            }
            
            if (!in_array($field_name, $valid_fields)) {
                $errors[] = "Invalid field '{$field_name}' for type '{$type}' in placeholder: {" . $placeholder_content . "}";
            }
        }
        
        return $errors;
    }

    /**
     * Pobierz dostępne placeholdery dla danego typu
     * 
     * @param string $type Typ rekordu (null dla wszystkich)
     * @return array Lista dostępnych placeholderów z opisami
     */
    public static function get_available_placeholders($type = null) {
        $field_configs = self::get_field_configuration();
        $placeholders = [];
        
        $types_to_process = $type ? [$type] : array_keys($field_configs);
        
        foreach ($types_to_process as $record_type) {
            $placeholders[$record_type] = [
                'name' => [
                    'placeholder' => "[{$record_type}.name]",
                    'description' => ucfirst($record_type) . ' name',
                    'example' => "[{$record_type}.name || 'Default Name']",
                    'required' => false,
                    'type' => 'text'
                ],
                'description' => [
                    'placeholder' => "[{$record_type}.description]",
                    'description' => ucfirst($record_type) . ' description', 
                    'example' => "[{$record_type}.description || 'No description']",
                    'required' => false,
                    'type' => 'textarea'
                ]
            ];
            
            if (isset($field_configs[$record_type])) {
                foreach ($field_configs[$record_type] as $field_name => $field_config) {
                    $placeholders[$record_type][$field_name] = [
                        'placeholder' => "[{$record_type}.{$field_name}]",
                        'description' => $field_config['label'] ?? ucfirst($field_name),
                        'example' => "[{$record_type}.{$field_name} || 'Default value']",
                        'required' => $field_config['required'] ?? false,
                        'type' => $field_config['type'] ?? 'text'
                    ];
                }
            }
        }
        
        return $placeholders;
    }

    /**
     * Generuj help text dla formularzy z dostępnymi placeholderami
     * 
     * @param array $types Typy do uwzględnienia (null dla wszystkich)
     * @return string HTML z pomocą
     */
    public static function generate_placeholders_help($types = null) {
        $placeholders = self::get_available_placeholders();
        
        if ($types) {
            $placeholders = array_intersect_key($placeholders, array_flip($types));
        }
        
        $html = '<div class="placeholders-help">';
        $html .= '<h5>' . get_string('available_placeholders', 'mod_valuemapdoc') . '</h5>';
        
        foreach ($placeholders as $type => $fields) {
            $html .= '<div class="placeholder-type mb-3">';
            $html .= '<h6 class="text-primary">' . ucfirst($type) . '</h6>';
            $html .= '<div class="row">';
            
            foreach ($fields as $field_name => $field_info) {
                $html .= '<div class="col-md-6 mb-2">';
                $html .= '<code>' . htmlspecialchars($field_info['placeholder']) . '</code>';
                $html .= '<br><small class="text-muted">' . htmlspecialchars($field_info['description']) . '</small>';
                if ($field_info['required']) {
                    $html .= ' <span class="badge badge-warning">Required</span>';
                }
                $html .= '</div>';
            }
            
            $html .= '</div></div>';
        }
        
        $html .= '<div class="alert alert-info mt-3">';
        $html .= '<strong>Tip:</strong> Use <code>{field.name || "default value"}</code> to provide fallback values.';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Pobierz przykładowe szablony
     * 
     * @return array Przykładowe szablony z opisami
     */
    public static function get_template_examples() {
        return [
            'document_title' => [
                'name' => 'Document Title',
                'template' => 'Value Map: {market.name || "Market Analysis"} - {customer.name || "Customer"}',
                'description' => 'Template for document titles'
            ],
            'filename' => [
                'name' => 'File Name',
                'template' => 'valuemap_{market.name}_{customer.name}_{timestamp}',
                'description' => 'Template for generated file names'
            ],
            'summary' => [
                'name' => 'Executive Summary',
                'template' => 'Analysis of {customer.name || "the customer"} in {market.name || "target market"} sector. ' .
                            'Industry: {customer.industry || "not specified"}. ' .
                            'Key opportunity: {opportunity.name || "not defined"}.',
                'description' => 'Template for executive summaries'
            ],
            'email_subject' => [
                'name' => 'Email Subject',
                'template' => 'Proposal for {customer.name}: {opportunity.name || "Business Opportunity"}',
                'description' => 'Template for email subjects'
            ]
        ];
    }

    /**
     * Przykłady użycia metod placeholder replacement
     * 
     * @return void
     */
    public static function demonstrate_placeholder_usage() {
        
        echo "=== PRZYKŁADY UŻYCIA PLACEHOLDER REPLACEMENT ===\n\n";
        
        // Przykład 1: Podstawowe użycie z metodą klasy
        $template1 = "Rynek: {market.name}\nKlient: {customer.name}\nOsoba: {person.name}";
        echo "Szablon 1: $template1\n";
        echo "Wywołanie: markets::replace_placeholders_from_db(\$template1, \$market_id, \$customer_id, \$person_id, \$opportunity_id)\n\n";
        
        // Przykład 2: Z fallback używając metody klasy
        $template2 = "Analiza dla {customer.name || 'Nieznany klient'} z branży {customer.industry || 'nieustalonej'}";
        echo "Szablon 2: $template2\n";
        echo "Wywołanie: markets::replace_placeholders_advanced(\$template2, \$market, \$customer, \$person, \$opportunity)\n\n";
        
        // Przykład 3: Nazwa pliku używając metody klasy
        $filename_template = "raport_{market.name}_{customer.name}_{timestamp}";
        echo "Szablon nazwy pliku: $filename_template\n";
        echo "Wywołanie: markets::generate_filename_from_template(\$market_id, \$customer_id, \$person_id, \$opportunity_id, \$filename_template)\n\n";
        
        // Przykład 4: Walidacja używając metody klasy
        $errors = self::validate_template($template2);
        if (empty($errors)) {
            echo "Szablon 2 jest prawidłowy według markets::validate_template()!\n\n";
        } else {
            echo "Błędy w szablonie 2 według markets::validate_template(): " . implode(", ", $errors) . "\n\n";
        }
        
        // Przykład 5: Dostępne placeholdery używając metody klasy
        $placeholders = self::get_available_placeholders('market');
        echo "Dostępne placeholdery dla market według markets::get_available_placeholders('market'):\n";
        if (isset($placeholders['market'])) {
            foreach ($placeholders['market'] as $field => $info) {
                echo "- {$info['placeholder']} : {$info['description']}\n";
            }
        }
        
        echo "\n=== PRZYKŁADY RZECZYWISTEGO UŻYCIA W KODZIE ===\n\n";
        
        echo "// W kontrolerze Moodle:\n";
        echo "\$template = get_config('mod_valuemapdoc', 'document_template');\n";
        echo "\$processed = \\mod_valuemapdoc\\local\\markets::replace_placeholders_from_db(\n";
        echo "    \$template, \$market_id, \$customer_id, \$person_id, \$opportunity_id\n";
        echo ");\n\n";
        
        echo "// W formularzu przed zapisem:\n";
        echo "\$user_template = \$mform->get_data()->template;\n";
        echo "\$errors = \\mod_valuemapdoc\\local\\markets::validate_template(\$user_template);\n";
        echo "if (!empty(\$errors)) {\n";
        echo "    \$mform->set_error('template', implode('<br>', \$errors));\n";
        echo "}\n\n";
        
        echo "// Generowanie pliku:\n";
        echo "\$filename = \\mod_valuemapdoc\\local\\markets::generate_filename_from_template(\n";
        echo "    \$market_id, \$customer_id, \$person_id, \$opportunity_id,\n";
        echo "    'valuemap_{market.name}_{customer.name}_{timestamp}'\n";
        echo ");\n\n";
        
        echo "// Pomoc dla użytkownika:\n";
        echo "\$help_html = \\mod_valuemapdoc\\local\\markets::generate_placeholders_help(['market', 'customer']);\n";
        echo "echo \$help_html; // Wyświetli dostępne placeholdery\n\n";
    }


    /**
     * Kompatybilna wersja replace_placeholders (zachowana dla wstecznej kompatybilności)
     * 
     * @param string $text Tekst zawierający placeholdery do podmiany
     * @param array $market Dane rynku
     * @param array $customer Dane klienta  
     * @param array $person Dane osoby
     * @param array $opportunity Dane możliwości
     * @return string Tekst z podmienionymi wartościami
     */
    public static function replace_placeholders($text, $market, $customer, $person, $opportunity) {
        
        // Tablica mapowania placeholderów na wartości
        $replacements = array();
        
        // Market data
        if ($market) {
            $replacements['{market.name}'] = isset($market['name']) ? $market['name'] : '';
            $replacements['{market.description}'] = isset($market['description']) ? $market['description'] : '';
            $replacements['{market.market_info}'] = self::find_dynamic_field_value($market, 'market_info');
            $replacements['{market.basic_info}'] = self::find_dynamic_field_value($market, 'basic_info');
            $replacements['{market.external_trends}'] = self::find_dynamic_field_value($market, 'external_trends');
            $replacements['{market.current_approach}'] = self::find_dynamic_field_value($market, 'current_approach');
            $replacements['{market.market_size}'] = self::find_dynamic_field_value($market, 'market_size');
            $replacements['{market.competition_level}'] = self::find_dynamic_field_value($market, 'competition_level');
        }
        
        // Customer data
        if ($customer) {
            $replacements['{customer.name}'] = isset($customer['name']) ? $customer['name'] : '';
            $replacements['{customer.description}'] = isset($customer['description']) ? $customer['description'] : '';
            $replacements['{customer.industry}'] = self::find_dynamic_field_value($customer, 'industry');
            $replacements['{customer.external_triggers}'] = self::find_dynamic_field_value($customer, 'external_triggers');
            $replacements['{customer.internal_initiatives}'] = self::find_dynamic_field_value($customer, 'internal_initiatives');
            $replacements['{customer.company_size}'] = self::find_dynamic_field_value($customer, 'company_size');
            $replacements['{customer.annual_revenue}'] = self::find_dynamic_field_value($customer, 'annual_revenue');
            $replacements['{customer.decision_process}'] = self::find_dynamic_field_value($customer, 'decision_process');
        }
        
        // Person data
        if ($person) {
            $replacements['{person.name}'] = isset($person['name']) ? $person['name'] : '';
            $replacements['{person.description}'] = isset($person['description']) ? $person['description'] : '';
            $replacements['{person.role}'] = self::find_dynamic_field_value($person, 'role');
            $replacements['{person.opportunities_seen}'] = self::find_dynamic_field_value($person, 'opportunities_seen');
            $replacements['{person.risks_challenges}'] = self::find_dynamic_field_value($person, 'risks_challenges');
            $replacements['{person.influence_level}'] = self::find_dynamic_field_value($person, 'influence_level');
            $replacements['{person.contact_preference}'] = self::find_dynamic_field_value($person, 'contact_preference');
            $replacements['{person.motivation}'] = self::find_dynamic_field_value($person, 'motivation');
        }
        
        // Opportunity data
        if ($opportunity) {
            $replacements['{opportunity.name}'] = isset($opportunity['name']) ? $opportunity['name'] : '';
            $replacements['{opportunity.description}'] = isset($opportunity['description']) ? $opportunity['description'] : '';
            $replacements['{opportunity.opportunity_type}'] = self::find_dynamic_field_value($opportunity, 'opportunity_type');
            $replacements['{opportunity.market_context}'] = self::find_dynamic_field_value($opportunity, 'market_context');
            $replacements['{opportunity.internal_readiness}'] = self::find_dynamic_field_value($opportunity, 'internal_readiness');
            $replacements['{opportunity.estimated_value}'] = self::find_dynamic_field_value($opportunity, 'estimated_value');
            $replacements['{opportunity.probability}'] = self::find_dynamic_field_value($opportunity, 'probability');
            $replacements['{opportunity.timeline}'] = self::find_dynamic_field_value($opportunity, 'timeline');
            $replacements['{opportunity.competition}'] = self::find_dynamic_field_value($opportunity, 'competition');
        }
        
        // Wykonanie podmian w tekście - najpierw obsługa placeholderów z fallback
        $result = $text;
        
        // Wzorzec dla placeholderów z fallback: {field.name || "default value"}
        $fallback_pattern = '/\{([^}]+?)\s*\|\|\s*(["\'])(.*?)\2\}/';
        
        $result = preg_replace_callback($fallback_pattern, function($matches) use ($replacements) {
            $placeholder = '{' . trim($matches[1]) . '}';
            $default_value = $matches[3]; // Wartość domyślna bez cudzysłowów
            
            // Sprawdź czy mamy wartość dla tego placeholdera
            if (isset($replacements[$placeholder])) {
                $value = $replacements[$placeholder];
                // Jeśli wartość jest pusta lub null, użyj domyślnej
                return (!empty($value)) ? $value : $default_value;
            }
            
            // Jeśli nie znamy tego placeholdera, zwróć wartość domyślną
            return $default_value;
        }, $result);
        
        // Następnie obsługa zwykłych placeholderów bez fallback
        foreach ($replacements as $placeholder => $value) {
            // Escape special characters in placeholder for regex
            $escaped_placeholder = preg_quote($placeholder, '/');
            $result = preg_replace('/' . $escaped_placeholder . '/', $value, $result);
        }
        
        return $result;
    }

// - dynamic replacement
}