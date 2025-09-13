<?php
namespace mod_valuemapdoc\form;

require_once($CFG->dirroot.'/course/moodleform_mod.php');

use moodleform;
use moodle_url;
use html_writer;

class rate_content_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $customdata['id']);

        $mform->addElement('hidden', 'docid');
        $mform->setType('docid', PARAM_INT);
        $mform->setDefault('docid', $customdata['docid']);

        $documentcontent = $customdata['documentcontent'];
        
        // Kontener główny
        $mform->addElement('html', '<div class="container-fluid mt-3">');
        $mform->addElement('html', '<div class="row">');
        
        // LEWA KOLUMNA – AKCJE I USTAWIENIA
        $mform->addElement('html', '<div class="col-md-3 rate-content-sidebar">');
        
        // Sekcja: Akcje na dokumencie
        $mform->addElement('html', '<div class="card mb-3">');
        $mform->addElement('html', '<div class="card-header"><h6 class="mb-0">' . get_string('documentactions', 'mod_valuemapdoc') . '</h6></div>');
        $mform->addElement('html', '<div class="card-body">');
        
        // Grupa przycisków eksportu - Bootstrap 4
        $mform->addElement('submit', 'savefile', get_string('saveasfile', 'mod_valuemapdoc'), [
            'class' => 'btn btn-primary btn-sm btn-block mb-2'
        ]);
        $mform->addElement('submit', 'sendmail', get_string('sendbymail', 'mod_valuemapdoc'), [
            'class' => 'btn btn-success btn-sm btn-block mb-2'
        ]);
        $mform->addElement('button', 'copytoclipboard', get_string('copytoclipboard', 'mod_valuemapdoc'), [
            'type' => 'button',
            'class' => 'btn btn-secondary  btn-block mb-2',
            'id' => 'id_copytoclipboard',
        ]);
                // Przycisk powrotu
        $mform->addElement('cancel', '', get_string('back'), [
            'class' => 'btn btn-secondary btn-sm btn-block mb-2'
        ]);
        

        
        $mform->addElement('html', '</div></div>'); // card-body, card
        
        // Sekcja: Ustawienia
        $mform->addElement('html', '<div class="card mb-3">');
        $mform->addElement('html', '<div class="card-header"><h6 class="mb-0">' . get_string('documentsettings', 'mod_valuemapdoc') . '</h6></div>');
        $mform->addElement('html', '<div class="card-body">');
        
        // Pole widoczności - Bootstrap 4
        $visibility_options = [
            0 => get_string('visibility_shared', 'mod_valuemapdoc'),
            1 => get_string('visibility_private', 'mod_valuemapdoc')
        ];
        $mform->addElement('select', 'visibility', get_string('visibility', 'mod_valuemapdoc'), $visibility_options, [
            'class' => 'form-controlx form-control-sm mb-2'
        ]);
        
        // Przycisk szybkiego zapisu
        $mform->addElement('submit', 'quicksave', get_string('save'), [
            'class' => 'btn btn-sm'
        ]);
        
        $mform->addElement('html', '</div></div>'); // card-body, card
        
        $mform->addElement('html', '</div>'); // col-md-3

        // PRAWA KOLUMNA – PODGLĄD Z AKCJAMI
        $mform->addElement('html', '<div class="col-md-9">');
        
        // TOOLBAR NAD TREŚCIĄ
        $this->add_content_toolbar($customdata);
        
        // KONTENER Z TREŚCIĄ
        $mform->addElement('html', '<div class="border rounded p-4 bg-light content-preview" style="max-height: 60vh; overflow-y: auto; min-height: 400px;">');
        $mform->addElement('html', '<div id="document-content-text">' . format_text($documentcontent, FORMAT_HTML) . '</div>');
        $mform->addElement('html', '</div>');
        
        // SEKCJA OCENY
        $mform->addElement('html', '<div class="mt-4">');
        $mform->addElement('html', '<div class="card">');
        $mform->addElement('html', '<div class="card-header"><h5 class="mb-0">' . get_string('ratecontent', 'mod_valuemapdoc') . '</h5></div>');
        $mform->addElement('html', '<div class="card-body">');

        // Pole komentarza
        $mform->addElement('textarea', 'feedback', get_string('ratefeedback', 'mod_valuemapdoc'), [
            'rows' => 4,
            'class' => 'form-controlx',
            'placeholder' => get_string('optionalcomment', 'mod_valuemapdoc')
        ]);
        $mform->setType('feedback', PARAM_TEXT);

        // Pole oceny
        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'effectiveness', '', 
            '<span class="text-success"><i class="fa fa-thumbs-up"></i> ' . get_string('useful_yes', 'mod_valuemapdoc') . '</span>', 1);
        $radioarray[] = $mform->createElement('radio', 'effectiveness', '', 
            '<span class="text-warning"><i class="fa fa-meh-o"></i> ' . get_string('useful_maybe', 'mod_valuemapdoc') . '</span>', 0);
        $radioarray[] = $mform->createElement('radio', 'effectiveness', '', 
            '<span class="text-danger"><i class="fa fa-thumbs-down"></i> ' . get_string('useful_no', 'mod_valuemapdoc') . '</span>', -1);

        $mform->addGroup($radioarray, 'effectivenessgroup', get_string('rategeneratedfile', 'mod_valuemapdoc'), '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', false);
        $mform->addRule('effectivenessgroup', null, 'required', null, 'client');
        $mform->setType('effectiveness', PARAM_INT);

        // Przyciski główne
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'), ['class' => 'btn btn-primary']);
        $buttonarray[] = $mform->createElement('cancel','', get_string('back'), ['class' => 'btn btn-secondary']);
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        
        $mform->addElement('html', '</div></div>'); // card-body, card
        $mform->addElement('html', '</div>'); // mt-4

        $mform->addElement('html', '</div>'); // col-md-9
        $mform->addElement('html', '</div>'); // row
        $mform->addElement('html', '</div>'); // container-fluid
    }

    /**
     * Dodaje toolbar z akcjami nad treścią dokumentu
     */
    private function add_content_toolbar($customdata) {
        $mform = $this->_form;
        
        // URLs dla akcji
        $actiontune = new moodle_url('/mod/valuemapdoc/tune_content.php', [
            'id' => $customdata['id'],
            'docid' => $customdata['docid']
        ]);

        $actionedit = new moodle_url('/mod/valuemapdoc/edit_content.php', [
            'id' => $customdata['id'],
            'docid' => $customdata['docid']
        ]);

        $actiondelete = new moodle_url('/mod/valuemapdoc/delete_content.php', [
            'id' => $customdata['id'],
            'docid' => $customdata['docid'],
            'sesskey' => sesskey()
        ]);

        // Toolbar - Bootstrap 4
        $toolbar_html = '<div class="d-flex justify-content-between align-items-center mb-3 p-3 content-toolbar">';
        
        // Lewa strona - tytuł
        $toolbar_html .= '<h5 class="mb-0 text-dark">';
        $toolbar_html .= '<i class="fa fa-file-text-o"></i> ' . get_string('documentpreview', 'mod_valuemapdoc');
        $toolbar_html .= '</h5>';
        
        // Prawa strona - przyciski akcji Bootstrap 4
        $toolbar_html .= '<div class="btn-group btn-group-sm" role="group">';
        
        // Przycisk edycji
        $toolbar_html .= html_writer::link(
            $actionedit,
            '<i class="fa fa-edit"></i> ' . get_string('edit'),
            [
                'class' => 'btn btn-primary',
                'title' => get_string('editcontent', 'mod_valuemapdoc')
            ]
        );
        
        // Przycisk dostrajania
        $toolbar_html .= html_writer::link(
            $actiontune,
            '<i class="fa fa-cog"></i> ' . get_string('tune', 'mod_valuemapdoc'),
            [
                'class' => 'btn btn-info',
                'title' => get_string('tunecontent', 'mod_valuemapdoc')
            ]
        );
        
        // Przycisk usuwania
        $toolbar_html .= html_writer::link(
            $actiondelete,
            '<i class="fa fa-trash"></i> ' . get_string('delete'),
            [
                'class' => 'btn btn-danger',
                'title' => get_string('deletecontent', 'mod_valuemapdoc'),
                'onclick' => "return confirm('" . get_string('confirmdelete', 'mod_valuemapdoc') . "');"
            ]
        );
        
        $toolbar_html .= '</div></div>';
        
        $mform->addElement('html', $toolbar_html);
    }

    /**
     * Obsługa różnych typów submitów
     */
    public function get_data() {
        $data = parent::get_data();
        
        if ($data) {
            // Sprawdź który przycisk został kliknięty
            if (isset($data->savefile)) {
                $data->action_type = 'save_file';
            } elseif (isset($data->sendmail)) {
                $data->action_type = 'send_mail';
            } elseif (isset($data->quicksave)) {
                $data->action_type = 'quick_save';
            } else {
                $data->action_type = 'full_save';
            }
        }
        
        return $data;
    }
}