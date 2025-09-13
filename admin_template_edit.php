<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('classes/form/admin_template_form.php');

global $DB,$USER;

$id = optional_param('id', 0, PARAM_INT);
admin_externalpage_setup('mod_valuemapdoc_templates');

if ($id) {
    $record = $DB->get_record('valuemapdoc_templates', ['id' => $id], '*', MUST_EXIST);
    $data['text'] = $record->templatebody; // weź tylko HTML
    $data['format'] = FORMAT_HTML; // ustaw format na HTML
    $record->templatebody = $data;
    $record->prompt = $record->prompt ?? '';
    $data['text'] = $record->description; // weź tylko HTML
    $data['format'] = FORMAT_HTML; // ustaw format na HTML
    $record->description = $record->description ?? '';
    $record->fields = $record->fields ?? '';

    $form = new \mod_valuemapdoc\form\admin_template_form(null, ['persistent' => true]);
    $form->set_data($record);
} else {
    $form = new \mod_valuemapdoc\form\admin_template_form();
}

// Obsługa formularza
if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/valuemapdoc/admin_templates.php'));
} else if ($data = $form->get_data()) {

    $record = new stdClass();
    $record->name = $data->name;
    $record->templatetype = $data->templatetype;
    $record->templatebody = $data->templatebody;
    $record->prompt = $data->prompt;
    $record->description = $data->description;
    $record->fields = $data->fields;
    $record->timemodified = time();
    $record->createdby = $USER->id;
    // Upewnij się, że pole 'templatebody' jest tekstem, nie tablicą:
    if (is_array($data->templatebody)) {
        $record->templatebody = $data->templatebody['text']; // weź tylko HTML
    }

    if (!empty($data->id)) {
        $record->id = $data->id;
        $DB->update_record('valuemapdoc_templates', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('valuemapdoc_templates', $record);
    }

    redirect(new moodle_url('/mod/valuemapdoc/admin_templates.php'), get_string('templatesaved', 'mod_valuemapdoc'), 2);
}

$PAGE->set_title(get_string('edittemplate', 'mod_valuemapdoc'));
$PAGE->set_heading(get_string('edittemplate', 'mod_valuemapdoc'));
echo $OUTPUT->header();
echo $OUTPUT->heading($id ? get_string('edittemplate', 'mod_valuemapdoc') : get_string('addtemplate', 'mod_valuemapdoc'));
$form->display();
echo $OUTPUT->footer();