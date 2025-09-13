<?php
// markets.php - główny kontroler

require_once('../../config.php');
require_once('classes/form/markets_form.php');
require_once('classes/local/markets.php');

use mod_valuemapdoc\form\markets_form;
use mod_valuemapdoc\local\markets;

$id = required_param('id', PARAM_INT); // Course module ID
$action = optional_param('action', 'list', PARAM_ALPHA);

// Parametry kontekstowe - określają automatycznie typ i relacje
$type = optional_param('type', '', PARAM_ALPHA);
$parentid = optional_param('parentid', 0, PARAM_INT);
$recordid = optional_param('recordid', 0, PARAM_INT);

// Sprawdź course module
$cm = get_coursemodule_from_id('valuemapdoc', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// URLs
$return_url = new moodle_url('/mod/valuemapdoc/view.php', ['id' => $id], 'markets-tab');
$page_url = new moodle_url('/mod/valuemapdoc/markets.php', ['id' => $id, 'action' => $action]);

$PAGE->set_url($page_url);
$PAGE->set_context($context);
$PAGE->set_title(format_string($valuemapdoc->name));
$PAGE->set_heading(format_string($course->fullname));

// Obsługa akcji
switch ($action) {
    
    case 'add':
        // AUTOMATYCZNE OKREŚLENIE TYPU na podstawie kontekstu
        if (empty($type)) {
            // Jeśli nie ma typu, domyślnie dodajemy market (najwyższy poziom)
            $type = 'market';
            $parentid = 0;
        }
        
        // Walidacja hierarchii - sprawdź czy można dodać ten typ do tego rodzica
        if ($parentid > 0) {
            $parent = markets::get_by_id($parentid);
            if (!$parent) {
                throw new moodle_exception('Parent not found');
            }
            
            // Sprawdź czy ten typ może być dzieckiem rodzica
            $allowed_children = markets::get_allowed_children($parent->type);
            if (!in_array($type, $allowed_children)) {
                throw new moodle_exception('Invalid parent-child relationship');
            }
            
            $parent_name = $parent->name;
            $parent_type = $parent->type;
        } else {
            // Brak rodzica - tylko market może nie mieć rodzica
            if ($type !== 'market') {
                redirect($return_url, 'Only markets can be added at the top level', null, 
                    \core\output\notification::NOTIFY_ERROR);
            }
            $parent_name = '';
            $parent_type = '';
        }
        
        // Sprawdź uprawnienia
       // require_capability('mod/valuemapdoc:addmarkets', $context);
        
        // Przygotuj dane dla formularza - DODAJ case 'add'
        $form_data = [
            'courseid' => $course->id,
            'cmid' => $cm->id,
            'type' => $type,
            'parentid' => $parentid,
            'parent_name' => $parent_name,
            'parent_type' => $parent_type,
            'recordid' => $recordid,
            'id' => $id,
            'is_edit' => false
        ];


        // Jeśli jest to edycja, dodaj dane rekordu
        
        $mform = new markets_form(null, $form_data);
        
        if ($mform->is_cancelled()) {
            redirect($return_url);
            
        } else if ($data = $mform->get_data()) {
            try {
                $new_id = markets::create($data);
                
                $type_name = ucfirst($type);
                \core\notification::success("$type_name has been added successfully");
                redirect($return_url);
                
            } catch (Exception $e) {
                \core\notification::error('Error adding ' . $type . ': ' . $e->getMessage());
            }
        }
        
        // Wyświetl formularz
        echo $OUTPUT->header();
        echo $OUTPUT->heading('Add ' . ucfirst($type));
        $mform->display();
        echo $OUTPUT->footer();
        break;
        
    case 'edit':
        if (empty($recordid)) {
            throw new moodle_exception('Record ID required');
        }
        
        $record = markets::get_by_id($recordid);
        if (!$record) {
            throw new moodle_exception('Record not found');
        }
        
        // Sprawdź uprawnienia
        if (!markets::can_edit($recordid, $context, $USER->id)) {
            throw new moodle_exception('No permission to edit this record');
        }
        
        // Pobierz informacje o rodzicu jeśli istnieje
        $parent_name = '';
        $parent_type = '';
        if ($record->parentid > 0) {
            $parent = markets::get_by_id($record->parentid);
            if ($parent) {
                $parent_name = $parent->name;
                $parent_type = $parent->type;
            }
        }
        
        // I w case 'edit':
        $form_data = [
            'courseid' => $course->id,
            'cmid' => $cm->id, // DODANE: ID instancji modułu
            'type' => $record->type,
            'parentid' => $record->parentid,
            'parent_name' => $parent_name,
            'parent_type' => $parent_type,
            'is_edit' => true,
            'recordid' => $recordid,
            'id' => $id
        ];

        $rec = $record;
        unset($rec->id);
        
        //var_dump($form_data);die();
        $mform = new markets_form(null, $form_data);
        $mform->set_data($rec);

        $data = $mform->get_data();

        if ($mform->is_cancelled()) {
            redirect($return_url);
        } else if ($data = $mform->get_data()) {
            try {                
                markets::update($recordid, $data);
                $type_name = ucfirst($record->type);
                \core\notification::success("$type_name has been updated successfully");
                redirect($return_url);
                
            } catch (Exception $e) {
                \core\notification::error('Error updating ' . $record->type . ': ' . $e->getMessage());
            }
        }
        
        // Wyświetl formularz
        echo $OUTPUT->header();
        echo $OUTPUT->heading('Edit ' . ucfirst($record->type) . ': ' . format_string($record->name));
        $mform->display();
        echo $OUTPUT->footer();
        break;
        
    case 'delete':
        if (empty($recordid)) {
            throw new moodle_exception('Record ID required');
        }
        
        require_sesskey();
        
        $record = markets::get_by_id($recordid);
        if (!$record) {
            throw new moodle_exception('Record not found');
        }
        
        // Sprawdź uprawnienia
        if (!markets::can_edit($recordid, $context, $USER->id)) {
            throw new moodle_exception('No permission to delete this record');
        }
        
        $confirm = optional_param('confirm', 0, PARAM_BOOL);
        
        if ($confirm) {
            try {
                // Sprawdź czy ma dzieci
                $children = markets::get_children($recordid);
                if (!empty($children)) {
                    // Usuń z dziećmi (cascade)
                    markets::delete_with_children($recordid);
                    $message = ucfirst($record->type) . ' and all related items have been deleted';
                } else {
                    // Usuń tylko ten element
                    markets::delete($recordid);
                    $message = ucfirst($record->type) . ' has been deleted';
                }
                
                \core\notification::success($message);
                redirect($return_url);
                
            } catch (Exception $e) {
                \core\notification::error('Error deleting ' . $record->type . ': ' . $e->getMessage());
                redirect($return_url);
            }
        } else {
            // Pokaż stronę potwierdzenia
            $PAGE->set_title('Delete ' . ucfirst($record->type));
            
            echo $OUTPUT->header();
            echo $OUTPUT->heading('Delete ' . ucfirst($record->type));
            
            // Sprawdź czy ma dzieci
            $children = markets::get_children($recordid);
            $has_children = !empty($children);
            
            if ($has_children) {
                $children_count = count($children);
                $warning = "This {$record->type} has {$children_count} related item(s). All will be deleted.";
                echo $OUTPUT->notification($warning, 'warning');
                
                echo '<ul>';
                foreach ($children as $child) {
                    echo '<li>' . ucfirst($child->type) . ': ' . format_string($child->name) . '</li>';
                }
                echo '</ul>';
            }
            
            $delete_url = new moodle_url('/mod/valuemapdoc/markets.php', [
                'id' => $id,
                'action' => 'delete',
                'recordid' => $recordid,
                'confirm' => 1,
                'sesskey' => sesskey()
            ]);
            
            $message = "Are you sure you want to delete this {$record->type}?";
            if ($has_children) {
                $message .= " This will also delete all related items.";
            }
            
            echo $OUTPUT->confirm($message, $delete_url, $return_url);
            echo $OUTPUT->footer();
        }
        break;
        
    default:
        // Lista - przekieruj do głównego widoku
        redirect($return_url);
}

/**
 * Helper function to get contextual breadcrumb
 */
function get_contextual_navigation($record) {
    if (!$record) return [];
    
    $breadcrumb = markets::get_breadcrumb($record->id);
    $nav = [];
    
    foreach ($breadcrumb as $item) {
        $nav[] = [
            'name' => $item->name,
            'type' => ucfirst($item->type),
            'url' => new moodle_url('/mod/valuemapdoc/view_market.php', [
                'id' => $id,
                'marketid' => $item->id
            ])
        ];
    }
    
    return $nav;
}