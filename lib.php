<?php
defined('MOODLE_INTERNAL') || die();


/**
 * Adds a new instance of the valuemapdoc module.
 *
 * @param stdClass $data
 * @param mod_valuemapdoc_mod_form $mform
 * @return int
 */
function valuemapdoc_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    // Ensure intro and format are stored
    $data->intro = $data->intro ?? '';
    $data->introformat = $data->introformat ?? FORMAT_HTML;
    $data->targetactivity = $data->targetactivity ?? 0;
    $data->ismaster = $data->ismaster ?? 0;
    $data->activity_prompt = $data->activity_prompt ?? '';

    return $DB->insert_record('valuemapdoc', $data);
}



/**
 * Updates an existing instance of the valuemapdoc module.
 *
 * @param stdClass $data
 * @param mod_valuemapdoc_mod_form $mform
 * @return bool
 */

function valuemapdoc_update_instance($data, $mform) {
    global $DB;

    $data->id = $data->instance;
//    $data->id = $data->id;
    $data->timemodified = time();

    // Standardowe pola opisu
    $data->intro = $data->intro;
    $data->introformat = $data->introformat;
    $data->targetactivity = $data->targetactivity ?? 0;
    $data->ismaster = $data->ismaster ?? 0;
    $data->activity_prompt = $data->activity_prompt ?? '';

    return $DB->update_record('valuemapdoc', $data);
}



/**
 * Deletes an instance of the valuemapdoc module.
 *
 * @param int $id
 * @return bool
 */
function valuemapdoc_delete_instance($id) {
    global $DB;

    // Sprawdź, czy instancja istnieje
    if (!$instance = $DB->get_record('valuemapdoc', ['id' => $id])) {
        return false;
    }

    // Usuń wpisy z mapy wartości powiązane z tą instancją
    $DB->delete_records('valuemapdoc_entries', ['valuemapdocid' => $id]);

    // Usuń powiązane dokumenty, jeśli masz tabelę np. valuemapdoc_documents
    $DB->delete_records('valuemapdoc_documents', ['valuemapdocid' => $id]);

    // Usuń instancję modułu
    $DB->delete_records('valuemapdoc', ['id' => $id]);

    return true;
}



function valuemapdoc_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_NO_VIEW_LINK:            return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_MOD_PURPOSE:             return MOD_PURPOSE_COLLABORATION;
        case FEATURE_BACKUP_MOODLE2:          return true;
        default: return null;
    }
}

function mod_valuemapdoc_page_init(moodle_page $page) {
    $page->requires->css('/mod/valuemapdoc/styles.css');
}

function mod_valuemapdoc_user_preferences(): array {
    return [
        'mod_valuemapdoc_masterfilters' => [
            'type' => PARAM_TEXT,
            'null' => null, // domyślna wartość
        ]
    ];
}

function mod_valuemapdoc_mobile_view($args) {
    global $DB, $OUTPUT;

    $cmid = $args['cmid'];
    $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);
    $valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);

    $data = new stdClass();
    $data->intro = format_module_intro('valuemapdoc', $valuemapdoc, $cm->id);
    $entries = $DB->get_records('valuemapdoc_entries', ['valuemapdocid' => $valuemapdoc->id]);
    $data->entries = array_values($entries); // array_values dla Mustache

    return [
        'templates' => [
            [
                'id' => 'main',
                'html' => $OUTPUT->render_from_template('mod_valuemapdoc/mobile_view', $data),
            ],
        ],
        'javascript' => '',
        'otherdata' => ['nocache' => true] // <-- wymusza odświeżenie za każdym razem
    ];
}

function valuemapdoc_update_grades($cm, $userid = 0, $nullifnone = true) {
    global $DB;

    $instance = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);

    $grade = new stdClass();
    $grade->userid = $userid;
    $grade->rawgrade = 1; // domyślna ocena 1 punkt – zmieniaj wg kontekstu
    $grade->dategraded = time();
    $grade->usermodified = $userid;

    grade_update('mod/valuemapdoc', $cm->course, 'mod', 'valuemapdoc', $cm->instance, $userid ? $userid : 0, $grade);
}

/**
 * Get available templates for a given course, group, and user.
 *
 * @param int $courseid
 * @param int $groupid
 * @param int $userid
 * @return array
 */
function valuemapdoc_get_available_templates($courseid, $groupid = 0, $userid = 0) {
    global $DB;
    
    $conditions = [];
    $params = [];
    
    // System templates (always available)
    $conditions[] = "(scope = 'system')";
    
    // Course templates
    if ($courseid > 0) {
        $conditions[] = "(scope = 'course' AND courseid = :courseid)";
        $params['courseid'] = $courseid;
    }
    
    // Group templates
    if ($groupid > 0) {
        $conditions[] = "(scope = 'group' AND courseid = :courseid2 AND groupid = :groupid)";
        $params['courseid2'] = $courseid;
        $params['groupid'] = $groupid;
    }
    
    // User templates
    if ($userid > 0) {
        $conditions[] = "(scope = 'user' AND courseid = :courseid3 AND userid = :userid)";
        $params['courseid3'] = $courseid;
        $params['userid'] = $userid;
    }
    
    $sql = "SELECT * FROM {valuemapdoc_templates} 
            WHERE isactive = 1 AND (" . implode(' OR ', $conditions) . ")
            ORDER BY scope DESC, name ASC";
            
    return $DB->get_records_sql($sql, $params);
}

/**
 * Check if the user can edit the template.
 *
 * @param stdClass $template
 * @param context $context
 * @param int $userid
 * @return bool
 */
function valuemapdoc_can_edit_template($template, $context, $userid) {
    switch ($template->scope) {
        case 'system':
            return has_capability('mod/valuemapdoc:managesystemtemplates', $context);
            
        case 'course':
            return has_capability('mod/valuemapdoc:managecoursetemplates', $context);
            
        case 'group':
            return has_capability('mod/valuemapdoc:managegrouptemplates', $context) &&
                   groups_is_member($template->groupid, $userid);
                   
        case 'user':
            return $template->userid == $userid || 
                   has_capability('mod/valuemapdoc:managealltemplates', $context);
    }
    return false;
}


/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * @param calendar_event $event
 * @param action_factory $factory
 * @return action_interface|null
 */
function valuemapdoc_core_calendar_provide_event_action(calendar_event $event, action_factory $factory) {
    // Value Map Doc module nie tworzy wydarzeń kalendarzowych
    return null;
}

/**
 * Refresh calendar events for valuemapdoc module.
 * 
 * This function is called by the calendar refresh adhoc task to update calendar events
 * for the valuemapdoc module. Since this module doesn't create calendar events,
 * we simply return true to indicate successful completion.
 *
 * @param int $courseid Course ID (0 for all courses)
 * @param int $instance Module instance ID (0 for all instances)  
 * @param int $moduleid Module ID (0 for all modules)
 * @return bool Success
 */
function valuemapdoc_refresh_events($courseid = 0, $instance = 0, $moduleid = 0) {
    // Ten moduł nie tworzy wydarzeń kalendarzowych, więc po prostu zwracamy success
    return true;
}


/**
 * Returns all other caps used in module
 * 
 * @return array
 */
function valuemapdoc_get_extra_capabilities() {
    return array();
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * 
 * @param stdClass $data the data submitted from the reset course.
 * @return array status array
 */
function valuemapdoc_reset_userdata($data) {
    return array();
}

/**
 * Returns information about received completion status
 * 
 * @param object $valuemapdoc
 * @param int $userid
 * @return bool
 */
function valuemapdoc_get_completion_state($valuemapdoc, $userid) {
    return false;
}

