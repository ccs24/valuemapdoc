<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

global $DB;

admin_externalpage_setup('mod_valuemapdoc_templates');

$notice= '';
// Obsługa usuwania
if ($deleteid = optional_param('delete', 0, PARAM_INT)) {
    require_sesskey();
    $usage = $DB->count_records('valuemapdoc_content', ['templateid' => $deleteid]);
    if ($usage == 0) {
        $DB->delete_records('valuemapdoc_templates', ['id' => $deleteid]);
        redirect(new moodle_url('/mod/valuemapdoc/admin_templates.php'), get_string('templatedeleted', 'mod_valuemapdoc'), 2);
    } else {
        $notice = $OUTPUT->notification(get_string('cannotdeleteused', 'mod_valuemapdoc'), 'notifyproblem');
    }
}

$duplicateid = optional_param('duplicate', 0, PARAM_INT);
if ($duplicateid && confirm_sesskey()) {
    $original_template = $DB->get_record('valuemapdoc_templates', ['id' => $duplicateid], '*', MUST_EXIST);
    
    // Utwórz nowy rekord z danymi z oryginalnego szablonu
    $new_template = new stdClass();
    $new_template->name = get_string('copyof', 'mod_valuemapdoc', $original_template->name);
    $new_template->templatetype = $original_template->templatetype;
    $new_template->description = $original_template->description;
    $new_template->fields = $original_template->fields;
    $new_template->templatebody = $original_template->templatebody;
    $new_template->prompt = $original_template->prompt;
    $new_template->timecreated = time();
    $new_template->timemodified = time();
    $new_template->usermodified = $USER->id;
    
    try {
        $new_id = $DB->insert_record('valuemapdoc_templates', $new_template);
        
        // Przekieruj do edycji nowego szablonu
        $edit_url = new moodle_url('/mod/valuemapdoc/admin_template_edit.php', ['id' => $new_id]);
        
        // Pokaż powiadomienie o sukcesie
        \core\notification::success(get_string('templateduplicatedsuccess', 'mod_valuemapdoc', $original_template->name));
        
        redirect($edit_url);
        
    } catch (Exception $e) {
        \core\notification::error(get_string('templateduplicationfailed', 'mod_valuemapdoc'));
    }
}

// Pobierz dane szablonów
$templates = $DB->get_records('valuemapdoc_templates');

// Przygotuj dane dla szablonu Mustache
$templatedata = new stdClass();
$templatedata->heading = get_string('templatesadmin', 'mod_valuemapdoc');
$templatedata->notice = $notice;
$templatedata->addtemplateurl = (new moodle_url('/mod/valuemapdoc/admin_template_edit.php'))->out(false);
$templatedata->addtemplatetext = get_string('addtemplate', 'mod_valuemapdoc');
$templatedata->has_templates = !empty($templates);
$templatedata->existingtemplatestext = get_string('existingtemplates', 'mod_valuemapdoc');

// Nagłówki tabeli
$templatedata->headers = [
    ['name' => get_string('templatename', 'mod_valuemapdoc')],
    ['name' => get_string('templatetype', 'mod_valuemapdoc')],
    ['name' => get_string('description', 'mod_valuemapdoc')],
    ['name' => get_string('usage', 'mod_valuemapdoc')],
    ['name' => get_string('actions', 'mod_valuemapdoc')]
];

// Przygotuj dane szablonów pogrupowane według typu
$templatedata->template_groups = [];
if ($templates) {
    // Grupuj szablony według typu
    $grouped_templates = [];
    foreach ($templates as $template) {
        $type = $template->templatetype ?: get_string('uncategorized', 'mod_valuemapdoc');
        if (!isset($grouped_templates[$type])) {
            $grouped_templates[$type] = [];
        }
        $grouped_templates[$type][] = $template;
    }
    
    // Sortuj grupy alfabetycznie
    ksort($grouped_templates);
    
    // Przygotuj dane dla każdej grupy
    foreach ($grouped_templates as $type => $type_templates) {
        $group = new stdClass();
        $group->typename = s($type);
        $group->templates = [];
        $group->template_count = count($type_templates);
        
        foreach ($type_templates as $template) {
            $usage = $DB->count_records('valuemapdoc_content', ['templateid' => $template->id]);
            
            $templaterow = new stdClass();
            $templaterow->id = $template->id;
            $templaterow->name = format_string($template->name);
            $templaterow->description = format_text($template->description ?? '', FORMAT_PLAIN);
            $templaterow->usage = $usage;
            $templaterow->can_delete = ($usage == 0);
            
            // URLs dla akcji
            $templaterow->editurl = (new moodle_url('/mod/valuemapdoc/admin_template_edit.php', 
                ['id' => $template->id]))->out(false);
                // URL dla duplikowania
            $templaterow->duplicateurl = (new moodle_url('/mod/valuemapdoc/admin_templates.php', [
                'duplicate' => $template->id,
                'sesskey' => sesskey()
            ]))->out(false);
            $templaterow->duplicatetext = get_string('duplicate', 'mod_valuemapdoc');
            
            $templaterow->edittext = get_string('edit');
            
            if ($usage == 0) {
                $templaterow->deleteurl = (new moodle_url('/mod/valuemapdoc/admin_templates.php', [
                    'delete' => $template->id,
                    'sesskey' => sesskey()
                ]))->out(false);
                $templaterow->deletetext = get_string('delete');
            } else {
                $templaterow->cannotdeletetext = get_string('cannotdeleteused', 'mod_valuemapdoc');
            }
            
            $group->templates[] = $templaterow;
        }
        
        $templatedata->template_groups[] = $group;
    }
}

// Renderuj szablon
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_valuemapdoc/admin_templates', $templatedata);
echo $OUTPUT->footer();
