<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_valuemapdoc\local;

defined('MOODLE_INTERNAL') || die();

//use function get_user_preference;  
//use function set_user_preference; 


/**
 * Helper class for managing field visibility levels in Value Map module
 *
 * @package    mod_valuemapdoc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_levels {
    
    /** @var string Default level for new users */
    const DEFAULT_LEVEL = 'basic';
    
    /** @var string User preference key */
    const PREFERENCE_KEY = 'mod_valuemapdoc_field_level';
    
    /**
     * Get all available levels with their configuration
     *
     * @return array Array of level definitions
     */
    public static function get_levels() {
        return [
            'basic' => [
                'name' => \get_string('level_basic', 'mod_valuemapdoc'),
                'description' => \get_string('level_basic_desc', 'mod_valuemapdoc'),
                'fields_count' => 7,
                'fields' => [
                    'market',
                    'role', 
                    'businessgoal',
                    'strategy',
                    'situation',
                    'differentiator',
                    'newstate'
                ]
            ],
            'valueproposition' => [
                'name' => \get_string('level_valueproposition', 'mod_valuemapdoc'),
                'description' => \get_string('level_valueproposition_desc', 'mod_valuemapdoc'),
                'fields_count' => 13,
                'fields' => [
                    // PODSTAWOWY (7 pól)
                    'market',
                    'role', 
                    'businessgoal',
                    'strategy',
                    'situation',
                    'differentiator',
                    'newstate',
                    // VALUE PROPOSITION dodatkowe (6 pól)
                    'industry',
                    'difficulty',
                    'coi',
                    'impactstrategy',
                    'impactbusinessgoal',
                    'clientname'
                ]
            ],
            'marketing' => [
                'name' => \get_string('level_marketing', 'mod_valuemapdoc'),
                'description' => \get_string('level_marketing_desc', 'mod_valuemapdoc'),
                'fields_count' => 20,
                'fields' => [
                    // PODSTAWOWY (7 pól)
                    'market',
                    'role', 
                    'businessgoal',
                    'strategy',
                    'situation',
                    'differentiator',
                    'newstate',
                    // VALUE PROPOSITION dodatkowe (6 pól)
                    'industry',
                    'difficulty',
                    'coi',
                    'impactstrategy',
                    'impactbusinessgoal',
                    'clientname',
                    // MARKETING dodatkowe (7 pól)
                    'statusquo',
                    'impact',
                    'successmetric',
                    'impactothers',
                    'proof',
                    'time2results',
                    'quote'
                ]
            ]
        ];
    }
    



/**
 * Get user's current field level with explicit preference
 */
public static function get_user_level_from_preference($preference) {
    $levels = self::get_levels();

    if (!array_key_exists($preference, $levels)) {
        return self::DEFAULT_LEVEL;
    }

    return $preference;
}

    /**
     * Get user's current field level
     *
     * @param int|null $userid User ID (null for current user)
     * @return string Current level key
     */
     
    public static function get_user_level($userid = null) {
        global $USER, $CFG, $DB;
        
        if (!\isloggedin()) {
            return self::DEFAULT_LEVEL; // Default level for guests 
        }

        if ($userid === null) {
            $userid = $USER->id;
        }

//        require_once('../../config.php');


//var_dump(get_defined_functions()['user']); // pokaże funkcje użytkownika
//die(); // zatrzymaj tutaj że


        // Upewnij się że Moodle jest załadowany
//        if (!\function_exists('\get_user_preference')) {
//            require_once($CFG->dirroot . '/lib/moodlelib.php');
//            require_once($CFG->dirroot.'/user/externallib.php');
//        }

        $preference = get_user_preferences(self::PREFERENCE_KEY, self::DEFAULT_LEVEL, $userid);

        // Validate that the preference is a valid level
        $levels = self::get_levels();
        if (!array_key_exists($preference, $levels)) {
            return self::DEFAULT_LEVEL;
        }
        
        return $preference;
    }
        
    
    /**
     * Set user's field level
     *
     * @param string $level Level key to set
     * @param int|null $userid User ID (null for current user)
     * @return bool Success status
     */
    public static function set_user_level($level, $userid = null) {
        global $USER;
        
        if ($userid === null) {
            $userid = $USER->id;
        }
        
        // Validate level
        $levels = self::get_levels();
        if (!array_key_exists($level, $levels)) {
            return false;
        }
        
        return \set_user_preference(self::PREFERENCE_KEY, $level, $userid);
    }
    
    /**
     * Get fields for user's current level
     *
     * @param int|null $userid User ID (null for current user)
     * @return array Array of field names
     */
    public static function get_user_fields($userid = null) {
        $level = self::get_user_level($userid);
        $levels = self::get_levels();
        
        return $levels[$level]['fields'] ?? $levels[self::DEFAULT_LEVEL]['fields'];
    }
    
    /**
     * Check if field should be visible for user's current level
     *
     * @param string $fieldname Field name to check
     * @param int|null $userid User ID (null for current user)
     * @return bool True if field should be visible
     */
    public static function is_field_visible($fieldname, $userid = null) {
        $userfields = self::get_user_fields($userid);
        return in_array($fieldname, $userfields);
    }
    
    /**
     * Get level configuration for user
     *
     * @param int|null $userid User ID (null for current user)
     * @return array Level configuration array
     */
    public static function get_user_level_config($userid = null) {
        $level = self::get_user_level($userid);
        $levels = self::get_levels();
        
        return $levels[$level] ?? $levels[self::DEFAULT_LEVEL];
    }
    
    /**
     * Filter columns array to show only fields visible for user's level
     *
     * @param array $columns Array of column definitions
     * @param int|null $userid User ID (null for current user)
     * @return array Filtered columns array
     */
    public static function filter_columns_for_user($columns, $userid = null) {
        $userfields = self::get_user_fields($userid);
        
        return array_filter($columns, function($column) use ($userfields) {
            // Always include non-field columns (like username, actions, etc.)
            if (!isset($column['field'])) {
                return true;
            }
            
            return in_array($column['field'], $userfields);
        });
    }
    
    /**
     * Get level options for select dropdown
     *
     * @return array Options array for form select element
     */
    public static function get_level_options() {
        $levels = self::get_levels();
        $options = [];
        
        foreach ($levels as $key => $config) {
            $options[$key] = $config['name'] . ' (' . $config['fields_count'] . ' ' . 
                            \get_string('fields', 'mod_valuemapdoc') . ')';
        }
        
        return $options;
    }
    
    /**
     * Get all field names from all levels (for database operations)
     *
     * @return array All possible field names
     */
    public static function get_all_fields() {
        $levels = self::get_levels();
        $allfields = [];
        
        foreach ($levels as $level) {
            $allfields = array_merge($allfields, $level['fields']);
        }
        
        return array_unique($allfields);
    }
}