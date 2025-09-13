<?php
require_once('../../config.php');
require_once('classes/local/session_helper.php');

use mod_valuemapdoc\local\session_helper;

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);
require_login($cm->course, true, $cm);


$PAGE->set_context($context);


// Wyczyść pracę w toku
session_helper::clear();

// Przekieruj użytkownika z powrotem na stronę widoku
redirect(
    new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id]));//,
