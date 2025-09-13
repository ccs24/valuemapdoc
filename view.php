<?php
require_once('../../config.php');

use mod_valuemapdoc\local\markets;

// use core\context\module as context_module;
// use core\output\notification;
// use core\exception\moodle_exception;

require_login();



$id = required_param('id', PARAM_INT); // course_module ID

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
//$PAGE->set_pagelayout('embedded'); // usunie header, footer i sidebar
$PAGE->set_url('/mod/valuemapdoc/view.php', ['id' => $id]);
$PAGE->set_title(get_string('pluginname', 'valuemapdoc'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);
$PAGE->requires->js_call_amd('mod_valuemapdoc/tabs', 'init');
// $PAGE->requires->js_call_amd('mod_valuemapdoc/bootstraploader', 'init'); // NIEUŻYWANE
$PAGE->requires->css(new moodle_url('/mod/valuemapdoc/styles/tabulator_bootstrap5.min.css'));
// $PAGE->requires->js(new moodle_url('/mod/valuemapdoc/amd/build/tabulator.min.js')); // NIEUŻYWANE
$PAGE->requires->js(new moodle_url('/mod/valuemapdoc/scripts/tabulator.min.js'),true); // <-- TO JEST KLUCZOWE
// $PAGE->requires->js_call_amd('mod_valuemapdoc/tabulatorlib', 'init'); // NIEUŻYWANE, tabulatormap inicjuje Tabulatora

//obsługa markets readonly
//$can_toggle_readonly = has_capability('mod/valuemapdoc:manage', $context) || has_capability('mod/valuemapdoc:manageentries', $context);
$can_toggle_readonly = true; // tymczasowo włączone, aby umożliwić przełączanie trybu readonly
// Klucz preferencji GLOBALNY dla użytkownika
$readonly_preference_key = 'mod_valuemapdoc_readonly_mode';

// Sprawdź czy jest parametr readonly w URL
$readonly_param = optional_param('readonly', null, PARAM_INT);

if ($readonly_param !== null && $can_toggle_readonly) {
    // Zapisz nową GLOBALNĄ preferencję użytkownika
    $new_readonly = (bool)$readonly_param;
    set_user_preferences(array($readonly_preference_key => $new_readonly ? '1' : '0'));
    $readonly_mode = $new_readonly;
    
    // Przekieruj aby usunąć parametr z URL
    $redirect_url = new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id],'markets-tab');
    redirect($redirect_url);
    
} else {
    // Odczytaj GLOBALNĄ preferencję użytkownika (domyślnie false = tryb edycji)
    $readonly_preference = get_user_preferences($readonly_preference_key, '0');
    $readonly_mode = (bool)$readonly_preference;
}

// Jeśli użytkownik nie ma uprawnień do edycji, wymuszaj readonly
$readonly_forced = false;
/*if (!has_capability('mod/valuemapdoc:manage', $context)) {
    $readonly_mode = true;
    $readonly_forced = true;
    $can_toggle_readonly = false;
}
*/

// Obsługa widoczności wg grupy

$groupmode = groups_get_activity_groupmode($cm);
$groupid = groups_get_activity_group($cm);
$usergroups = groups_get_user_groups($cm->course, $USER->id);
if ($groupmode == SEPARATEGROUPS && empty($usergroups[0]) && !has_capability('moodle/site:accessallgroups', $context)) {
    echo $OUTPUT->notification(get_string('nogroupaccess', 'mod_valuemapdoc'), 'error');
    echo $OUTPUT->footer();
    exit;
}

$groupusers = [];

if ($groupmode) {
    if ($groupid) {
        $groupusers = array_keys(groups_get_members($groupid, 'u.id'));
    } else {
        // Jeśli są grupy, ale nie wybrano żadnej – pokazujemy tylko własne wpisy
        $groupusers = [$USER->id];
    }
} else {
    // Brak trybu grupowego – pokazujemy wszystkie wpisy
    $groupusers = $DB->get_fieldset_select('user', 'id', 'deleted = 0');
}

// Pobierz wpisy widoczne dla użytkownika/grupy
list($insql, $params) = $DB->get_in_or_equal($groupusers, SQL_PARAMS_QM);

$params[] = $course->id;
$params['thismoduleid'] = $cm->instance;



$columns = [
    'market', 'industry', 'role', 'businessgoal', 'strategy', 'difficulty', 'situation',
    'statusquo', 'coi', 'differentiator', 'impact', 'newstate', 'successmetric',
    'impactstrategy', 'impactbusinessgoal', 'impactothers', 'proof', 'time2results',
    'quote', 'clientname'
];


    // Pobierz instancje master dla tego kursu
    $instances = $DB->get_records_sql("
            SELECT cm.id as cmid, v.name
            FROM {course_modules} cm
            JOIN {modules} m ON cm.module = m.id
            JOIN {valuemapdoc} v ON cm.instance = v.id
            WHERE cm.course = :courseid AND v.ismaster = 1 AND m.name = 'valuemapdoc'
        ", ['courseid' => $course->id]);

    $usergroupsall = groups_get_user_groups($course->id, $USER->id);
    $userhasgroups = !empty($usergroupsall[0]);

   // Obsługa wyboru instancji master
    $options = [['id' => 0, 'name' => 'LOCAL']];
    $rawpref = get_user_preferences("mod_valuemapdoc_masterfilters", '');
    $parts = explode(':', $rawpref);
    $selectedfilter = ($parts[0] == $cm->id) ? (int)($parts[1] ?? 0) : 0;

    foreach ($instances as $instance) {
      //  var_dump($instance);
            $icm  = get_coursemodule_from_id('valuemapdoc', $instance->cmid, 0, false, MUST_EXIST);
            $icontextmodule = \context_module::instance($icm->id);
            $igroupmode = groups_get_activity_groupmode($icm);

            if ($igroupmode == SEPARATEGROUPS && !$userhasgroups && !has_capability('moodle/site:accessallgroups', $contextmodule)) {
                continue; // użytkownik nie należy do żadnej grupy, a aktywność wymaga przynależności
            }
            $options[]  = [
                'id' => $instance->cmid, 
                'name' =>  $instance->name,
            ];
        }

    //Dodaj lokalną instancję jako opcję
    foreach ($options as $key => $option) {
        if ($option['id'] == $selectedfilter){
            $options[$key]['is_selected'] = true; // oznacz lokalny jako wybrany
        }
    }

    $hascourseanymaster = (count($options) >1 );//!empty($masterinstances);
    if ($hascourseanymaster) {
        $classmaster = "";
    } else {
        $classmaster = " d-none";
    }

// Zakładka: Content – dotychczasowa zawartość


$context = \context_module::instance($id);
//self::validate_context($context);
$params = ['cmid' => $id];
$sql = "
    SELECT c.id, c.userid, c.visibility, u.firstname, u.lastname,
           c.name, c.templateid, t.templatetype, t.name AS templatename,
           c.customprompt, c.marketid, c.customerid, c.personid, c.opportunityid, 
           c.timecreated, c.content
    FROM {valuemapdoc_content} c
    JOIN {user} u ON u.id = c.userid
    LEFT JOIN {valuemapdoc_templates} t ON t.id = c.templateid
    WHERE c.cmid = :cmid
";

if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
    $usergroups = groups_get_user_groups($course->id, $USER->id);
    if (empty($usergroups[0])) {
        $records = [];
    } else {
        $sql .= " AND (c.userid = :userid OR (c.visibility = 0 AND c.groupid = :groupid))";
        $params['userid'] = $USER->id;
        $params['groupid'] = $groupid;
        $records = $DB->get_records_sql($sql, $params);
    }
} else {
    $sql .= " AND (c.visibility = 0 OR c.userid = :userid)";
    $params['userid'] = $USER->id;
    $records = $DB->get_records_sql($sql, $params);
}

$results = [];
foreach ($records as $rec) {
    $results[] = [
        'id' => $rec->id,
        'userid' => $rec->userid,
        'name' => $rec->name ?? '',
        'templateid' => $rec->templateid ?? 0,
        'templatetype' => $rec->templatetype ?? '(brak)',
        'templatename' => $rec->templatename ?? '(brak)',
        'customprompt' => $rec->customprompt ?? '',
        //c.marketid, c.customerid, c.personid, c.opportunityid
        'market' => $rec->marketid ? markets::get_by_id($rec->marketid)->name : '(brak)',
        'customer' => $rec->customerid ? markets::get_by_id($rec->customerid)->name : '(brak)',
        'person' => $rec->personid  ? markets::get_by_id($rec->personid)->name : '(brak)',
        'opportunity' => $rec->opportunityid ? markets::get_by_id($rec->opportunityid)->name : '(brak)',
        'timecreated' => userdate($rec->timecreated),
        'content' => shorten_text(strip_tags($rec->content), 200),
        'visibility' => $rec->visibility ?? 0,
    ];
}


//$docs = $DB->get_records('valuemapdoc_content', ['cmid' => $id], 'userid', 'opportunityname, templateid, timecreated');
$grouped = [];
foreach ($results as $doc) {
    $oopname = $doc['market'] . ' - ' . $doc['customer'] . ' - ' . $doc['person'] . ' - ' . $doc['opportunity'];
    $grouped[$oopname][$doc['templatetype']][$doc['templatename']][] = $doc;
//    $grouped[$doc['templatetype']][$doc['templatename']][] = $doc;
}



// Zakładka: Template – dotychczasowa zawartość

$templates = $DB->get_records('valuemapdoc_templates', null, 'templatetype, name, description, fields', '*');
$grouped_templates = [];


foreach ($templates as $tpl) {
    $ttype = $tpl->templatetype;
    if (!isset($grouped_templates[$ttype])) {
        $grouped_templates[$ttype] = [
            'ttype' => $ttype,
            'items' => []
        ];
    }
    $grouped_templates[$ttype]['items'][] = [
        'tname' => $tpl->name,
        'description' => $tpl->description,
        'fields' => $tpl->fields
    ];
}



$templateselect = [];

foreach ($templates as $template) {
    $type = $template->templatetype;
    if (!isset($templateselect[$type])) {
        $templateselect[$type] = [
            'type' => $type,
            'items' => []
        ];
    }

    $templateselect[$type]['items'][] = [
        'id' => $template->id,
        'name' => $template->name
    ];
}

//var_dump($groupmode);
$groupoptions = [];
if ($groupmode != NOGROUPS) {
if (has_capability('moodle/site:accessallgroups', $context)) {
    $groups = groups_get_all_groups($course->id);
/*    $groupoptions[] = [
        'id' => 0,
        'name' => get_string('allgroups', 'group'),
        'is_selected' => ($groupid == 0)
    ]; */
    foreach ($groups as $group) {
        $groupoptions[] = [
            'id' => $group->id,
            'name' => $group->name,
            'is_selected' => ($group->id == $groupid)
        ];
    }
}

$haaccessallgroups = has_capability('moodle/site:accessallgroups', $context);
} else {
    $haaccessallgroups = false;
}


//$marketsurl = new moodle_url('/mod/valuemapdoc/markets.php', ['id' => $id]);
$marketsurl = (new moodle_url('/mod/valuemapdoc/markets.php', [
        'id' => $id,
        'action' => 'add',
        'type' => 'market'
]));
// W view.php
$markets = markets::get_by_type($cm->id, 'market');
$markets_html = markets::render_hierarchy_mustache($markets, $cm->id, false); // readonly mode

//echo ($markets_html);die();

//$markets = markets::get_by_type($course->id, 'market');
//$markets = markets::get_market_detail_template_data($market, $course->id, $cm->id);

$m = array_map(function($market) use ($cm) {
        return markets::get_market_compact_item_data($market, $cm->id, 0, false);
    }, $markets);

$m = markets::get_hierarchy_template_data($markets, $cm->id, $readonly_mode);
//var_dump($m);die();

$tablecontent = [
    'coursefullname' => $course->fullname,
    'courseid' => $course->id,
    'valuemapdocname' => format_string($valuemapdoc->name),
    'cmid' => $id,
    'groupmode' => $groupmode,
    'groupid' => $groupid,
    'generate_url' => new moodle_url('/mod/valuemapdoc/generate.php', ['id' => $id]),
    'edit_url' =>  new moodle_url('/mod/valuemapdoc/edit.php', ['id' => $id]), 
    'selectedfilter' => $selectedfilter,
    'hasmanagecapability' => has_capability('mod/valuemapdoc:manageentries', $context),
    'import_url' => new moodle_url('/mod/valuemapdoc/import.php', ['id' => $id]),
    'export_url' => new moodle_url('/mod/valuemapdoc/export.php', ['id' => $id]),
    'classmaster' => $classmaster,
    'masteroptions' => $options,
    'columns' => json_encode(array_map(fn($c) => [
        'title' => get_string($c, 'valuemapdoc'),
        'field' => $c,
        'hozAlign' => 'left',
        'headerSort' => true,
        'width' => 150
    ], $columns),JSON_HEX_APOS | JSON_HEX_QUOT),

    'tabulatordata' => [ // możesz tu przekazać dodatkowe dane potrzebne do js
        'courseid' => $course->id,
        'cmid' => $cm->id,
        'filtercmid' => $selectedfilter
    ],
    'documents' => $grouped, // przetworzone wpisy content
    'tempselect' => array_values($templateselect), // przetworzone szablony

    'templates' => array_values($grouped_templates),// przetworzone szablony
    'bulk_edit_url' => new moodle_url('/mod/valuemapdoc/edit_bulk.php', ['id' => $id]),
    'hasaccessallgroups' => $haaccessallgroups,
    'groupoptions' => $groupoptions,
    
    'has_markets' => !empty($markets),
    'readonly' => $readonly_mode,
    'markets' => array_values($m),
    'markets' => $m['markets'],  
    'has_markets' => $m['has_markets'], 
    'readonly' => $m['readonly'], 
    'add_market_url' => $m['add_market_url'],
    'marketsurl' => $marketsurl,

    'readonly_toggle' => [
        'can_toggle' => $can_toggle_readonly,
        'current_readonly' => $readonly_mode,
        'is_user_choice' => !$readonly_forced, // True jeśli użytkownik sam wybrał readonly
        'toggle_url' => new moodle_url('/mod/valuemapdoc/view.php', [
            'id' => $cm->id,
            'readonly' => $readonly_mode ? 0 : 1
        ]),
        'show_toggle' => $can_toggle_readonly
    ],
    'readonly_info' => [
        'is_forced' => $readonly_forced, // Wymuszony przez brak uprawnień
        'is_user_choice' => !$readonly_forced && $readonly_mode, // Wybór użytkownika
        'can_edit' => true , //has_capability('mod/valuemapdoc:manage', $context)
    ],
];


$renderer = $PAGE->get_renderer('mod_valuemapdoc');
$PAGE->requires->js_call_amd('mod_valuemapdoc/tabulatormap', 'init', [
    'courseid' => $course->id,
    'cmid' => $cm->id,
    'filtercmid' => $selectedfilter
]);

//var_dump($tablecontent); die();

echo $OUTPUT->header();
echo $renderer->render_from_template('mod_valuemapdoc/view', $tablecontent );
echo $OUTPUT->footer();