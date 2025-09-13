<?php
/**
 * External API for ValueMapDoc hierarchy - get_markets
 * File: /mod/valuemapdoc/classes/external/get_markets.php
 */

namespace mod_valuemapdoc\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/classes/local/markets.php');

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use context_module;
use mod_valuemapdoc\local\markets;

/**
 * ValueMapDoc external function for getting hierarchy children
 */
class get_markets extends external_api {

    /**
     * Returns description of method parameters for get_hierarchy_children
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'parentid' => new external_value(PARAM_INT, 'Parent record ID', VALUE_DEFAULT, 0),
            'childtype' => new external_value(PARAM_ALPHA, 'Child type to filter', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Get children records for hierarchy selector
     *
     * @param int $cmid Course module ID
     * @param int $parentid Parent record ID  
     * @param string $childtype Child type filter
     * @return array Children options
     */
    public static function execute($cmid, $parentid = 0, $childtype = '') {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'parentid' => $parentid,
            'childtype' => $childtype,
        ]);

        // Verify course module and permissions
        $cm = get_coursemodule_from_id('valuemapdoc', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        // Check access
        require_login($course, false, $cm);
        require_capability('mod/valuemapdoc:view', $context);

        // Get children based on parent and type
        $children = [];
        
        if ($params['parentid'] == 0) {
            // Root level - get markets or all types if no childtype specified
            if (empty($params['childtype']) || $params['childtype'] === 'market') {
                $children = markets::get_by_type($params['cmid'], markets::TYPE_MARKET);
            }
        } else {
            // Get children of specific parent
            $children = markets::get_children($params['parentid'], $params['childtype']);
        }

        // Prepare options
        $options = [];
        
        // Add empty option
        $placeholder_text = '-- Choose --';
        if (!empty($params['childtype'])) {
            $placeholder_text = '-- ' . ucfirst($params['childtype']) . ' --';
        }
        
        $options[] = [
            'value' => 0,
            'text' => $placeholder_text,
            'selected' => false
        ];

        // Add children options
        foreach ($children as $child) {
            $options[] = [
                'value' => (int)$child->id,
                'text' => format_string($child->name),
                'selected' => false
            ];
        }

        return [
            'options' => $options
        ];
    }

    
    /**
     * Returns description of method result value for get_hierarchy_children
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'options' => new external_multiple_structure(
                new external_single_structure([
                    'value' => new external_value(PARAM_INT, 'Option value'),
                    'text' => new external_value(PARAM_TEXT, 'Option text'),
                    'selected' => new external_value(PARAM_BOOL, 'Selected status'),
                ])
            ),
        ]);
    }
}