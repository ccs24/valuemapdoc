<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/valuemapdoc/lib.php');

use mod_valuemapdoc\local\markets;

$id = required_param('id', PARAM_INT); // Course module ID
$marketid = required_param('marketid', PARAM_INT); // Market record ID

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
$market = $DB->get_record('valuemapdoc_markets', ['id' => $marketid, 'isactive' => 1], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/valuemapdoc/view_market.php', ['id' => $id, 'marketid' => $marketid]);
$PAGE->set_title($market->name . ' - ' . format_string($valuemapdoc->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Navigation breadcrumbs
$PAGE->navbar->add($valuemapdoc->name, new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]));
$PAGE->navbar->add('Markets', new moodle_url('/mod/valuemapdoc/markets.php', ['id' => $id]));
$PAGE->navbar->add($market->name);

echo $OUTPUT->header();

// Render market details using Mustache
$template_data = markets::get_market_detail_template_data($market, $cm->id);
echo $OUTPUT->render_from_template('mod_valuemapdoc/market_detail', $template_data);

echo $OUTPUT->footer();