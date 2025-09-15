<?php

require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$templateid = optional_param('templateid', null, PARAM_RAW);


$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/valuemapdoc/view_content.php', ['id' => $id]));
$PAGE->set_title(get_string('viewcontent', 'mod_valuemapdoc'));
$PAGE->set_heading(get_string('viewcontent', 'mod_valuemapdoc'));
$PAGE->requires->css(new moodle_url('/mod/valuemapdoc/styles/tabulator_bootstrap5.min.css'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('generateddocuments', 'mod_valuemapdoc'));


$columns = json_encode([
    ['title' => 'ID', 'field' => 'id'],
//    ['title' => get_string('user'), 'field' => 'username'],
    ['title' => get_string('opportunityname', 'mod_valuemapdoc'), 'field' => 'opportunityname'],
    ['title' => get_string('templatetype', 'mod_valuemapdoc'), 'field' => 'templatetype'],
    ['title' => get_string('templatename', 'mod_valuemapdoc'), 'field' => 'templatename'],
    ['title' => get_string('time'), 'field' => 'timecreated'],
], JSON_HEX_APOS | JSON_HEX_QUOT);

// Zakładki
$valuemaptab = new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]);//->out();
$contenttab = new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id],'content-tab');//->out();
$templatetab = new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id],'templates-tab');//->out();
echo html_writer::start_div('container mt-3',
    ['id' => 'valuemapdoc-tabs-container']);

echo html_writer::start_div('row');
echo html_writer::start_div('col');


echo '<a href="' . $valuemaptab . '" class="btn btn-sm btn-outline-secondary me-1">Value Map</a>';

/*
echo "
<ul class='nav nav-tabs' role='tablist'>
  <li class='nav-item' role='presentation'>
    <a href='$valuemaptab' class='nav-link' id='valuemap-tab-btn'  
    type='button' >ValueMap</a>
  </li>";
echo '
  <li class="nav-item" role="presentation">
    <a href="'.$contenttab .'" class="nav-link active" id="content-tab-btn"  type="button" >Content</a>
  </li>
  <li class="nav-item" role="presentation">
    <a href="' . $templatetab . '" class="nav-link" id="templates-tab-btn"  type="button" >Templates</a>
  </li>
</ul>
';
*/
//echo html_writer::start_div('row');
//echo html_writer::start_div('col');

// Przycisk wróc do generatora
//echo $OUTPUT->single_button($url, get_string('backtogenerator', 'mod_valuemapdoc'), 'get');
echo html_writer::end_div();
echo html_writer::end_div();

/*
echo html_writer::start_div('col'); 
*/
// Przycisk uwzględnij rekordy master
/*echo html_writer::start_div('', ['style' => 'margin-bottom: 1rem']);
echo html_writer::checkbox('includemaster-toggle', 1, false,
    get_string('includeotherusersdocs', 'mod_valuemapdoc'),
    ['id' => 'includemaster-toggle']);
    */
/*
echo html_writer::label(get_string('includeotherusersdocs', 'mod_valuemapdoc'), 'includemaster-toggle');
echo html_writer::empty_tag('input', [
    'type' => 'checkbox',
    'id' => 'includemaster-toggle',
    'class' => 'form-check-input',
    'style' => 'margin-left: 0.5rem;',
    'checked' => true
    ]);
echo html_writer::end_div();
*/

// Pole szukaj
echo html_writer::start_div('col');
echo html_writer::start_div('', ['style' => 'margin-bottom: 1rem']);
//    echo html_writer::label(get_string('search'), 'valuemap-search');
/*
echo html_writer::empty_tag('input', [
 'type' => 'text',
 'id' => 'valuemap-search',
 'placeholder' => get_string('search'),
 'class' => 'form-control',
 'style' => 'max-width: 300px;'
]);
*/

echo html_writer::end_div();
echo html_writer::end_div();

/*
echo html_writer::start_div('col');
// Przycisk pełny ekran
echo html_writer::start_div('mb-1');
echo '<button id="toggle-fullscreen" class="btn btn-secondary">🔳 Pełny ekran</button>';
echo html_writer::end_div();
echo html_writer::end_div();
*/

echo '
      <button id="refresh-documents-btn" 
              class="btn btn-outline-primary btn-sm"
              type="button"
              title="' . get_string("refresh", "core") . '"
              >
              <i class="fa fa-refresh"></i>
              <span class="btn-text d-none d-sm-inline ms-1"> ' . get_string("refresh", "core") . ' </span>
              </button>';
              

echo html_writer::end_div();

//TABELA AJAX
echo html_writer::start_div('table-responsive');


echo html_writer::div('', 'valuemapdoc-content-table', [
    'id' => 'valuemapdoc-content-table',
    'data-columns' => $columns,
    'data-courseid' => $course->id,
    'data-cmid' => $cm->id,
    'data-templateid' => $templateid,
]);
echo html_writer::end_tag('div');
echo html_writer::end_div();

$PAGE->requires->js_call_amd('mod_valuemapdoc/tablecontent', 'init', [
    'courseid' => $course->id,
    'cmid' => $cm->id,
    'templateid' => $templateid,
]);

echo $OUTPUT->footer();