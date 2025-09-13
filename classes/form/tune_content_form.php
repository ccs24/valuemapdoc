<?php

namespace mod_valuemapdoc\form;

require_once('classes/local/session_helper.php');

use mod_valuemapdoc\local\session_helper;


use moodleform; 
use moodle_url;
use html_writer;

require_once("$CFG->libdir/formslib.php");

class tune_content_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Tytuł formularza
//        $mform->addElement('html', '<h3 style="text-align: center;">' . get_string('tune', 'mod_valuemapdoc') . '</h3>');

        // Sekcja główna (dwa podglądy)
        $mform->addElement('html', '<div style="display: flex; gap: 20px;">');

        // Kolumna 1: Aktualny tekst
        $mform->addElement('html', '<div style="flex: 1;">');
        $mform->addElement('html', html_writer::tag('h5', get_string('originaltext', 'mod_valuemapdoc')));
        $mform->addElement('html', html_writer::div(
            format_text($customdata['workingcontent'], FORMAT_HTML),
            'original-content-store',
            [   
                'id' => 'originaltext',
                'style' => 'color: #666',
            ]));

        
        $mform->addElement('html', '</div>');

        // Kolumna 2: Wygenerowany tekst
        $mform->addElement('html', '<div style="flex: 1;">');
        $mform->addElement('html', html_writer::tag('h5', get_string('tunedtext', 'mod_valuemapdoc')));
        $mform->addElement('html', html_writer::div(
            format_text($customdata['workingcontent'], FORMAT_HTML),
            'generated-content-preview',
            [
                'id' => 'newtext'
            ]));

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>'); // Koniec sekcji głównej

        // Nowy rząd: prompt + toolbar
$mform->addElement('html', html_writer::start_div('d-flex flex-column align-items-end mt-4', [
    'style' => 'gap: 15px;',
]));

// Toolbar - przyciski tuningowe
$mform->addElement('html', '
<div class="btn-toolbar" role="toolbar" aria-label="' . get_string('tuningoptionstoolbar', 'mod_valuemapdoc') . '">
    <div class="btn-group" role="group" aria-label="' . get_string('tuninggroup', 'mod_valuemapdoc') . '">
        <button type="button" class="btn btn-outline-primary btn-lg prompt-preset" 
            data-prompt="'. get_string('formal_prompt', 'mod_valuemapdoc') . '"
            id="formal-button"
            data-bs-toggle="tooltip" data-bs-placement="top"
            title="' . get_string('tooltip_formal', 'mod_valuemapdoc') . '">
            <i class="fa fa-briefcase" aria-hidden="true"></i> ' . get_string('formal', 'mod_valuemapdoc') . '
        </button>
        <button type="button" class="btn btn-outline-primary btn-lg prompt-preset" 
            data-prompt="'. get_string('friendly_prompt', 'mod_valuemapdoc') . '"
            id="friendly-button"
            data-bs-toggle="tooltip" data-bs-placement="top"
            title="' . get_string('tooltip_friendly', 'mod_valuemapdoc') . '">
            <i class="fa fa-smile" aria-hidden="true"></i> ' . get_string('friendly', 'mod_valuemapdoc') . '
        </button>
        <button type="button" class="btn btn-outline-primary btn-lg prompt-preset" 
            data-prompt="'. get_string('short_prompt', 'mod_valuemapdoc') . '"
            id="short-button"
            data-bs-toggle="tooltip" data-bs-placement="top"
            title="' . get_string('tooltip_short', 'mod_valuemapdoc') . '">
            <i class="fa fa-compress" aria-hidden="true"></i> ' . get_string('short', 'mod_valuemapdoc') . '
        </button>
        <button type="button" class="btn btn-outline-primary btn-lg prompt-preset" 
            data-prompt="'. get_string('value_prompt', 'mod_valuemapdoc') . '"
            id="value-button"
            data-bs-toggle="tooltip" data-bs-placement="top"
            title="' . get_string('tooltip_value', 'mod_valuemapdoc') . '">
            <i class="fa fa-gift" aria-hidden="true"></i> ' . get_string('value', 'mod_valuemapdoc') . '
        </button>
        <button type="button" class="btn btn-outline-primary btn-lg prompt-preset" 
            data-prompt="'. get_string('dynamic_prompt', 'mod_valuemapdoc') . '"
            id="dynamic-button"
            data-bs-toggle="tooltip" data-bs-placement="top"
            title="' . get_string('tooltip_dynamic', 'mod_valuemapdoc') . '">
            <i class="fa fa-bolt" aria-hidden="true"></i> ' . get_string('dynamic', 'mod_valuemapdoc') . '
        </button>
    </div>
</div>
');

$mform->addElement('html', html_writer::start_div('d-flex align-items-center mt-3', [
    'style' => 'gap: 10px;',
]));
// Krótki tekst
$mform->addElement('html', html_writer::tag('span',
    get_string('promptlabel', 'mod_valuemapdoc'),
    ['class' => 'text-muted small']
));
// Input Prompt ręcznie
$mform->addElement('html', '<input type="text" id="tuningprompt" name="tuningprompt" 
class="form-control" style=" width: 400px;" placeholder="' . get_string('promptplaceholder', 'mod_valuemapdoc') . '">');

// Button SEND ręcznie
$mform->addElement('html', html_writer::tag('button',
    html_writer::tag('i', '', ['class' => 'fa fa-paper-plane', 'aria-hidden' => 'true']),
    [
        'type' => 'button',
        'class' => 'btn btn-success btn-sm ms-2',
        'id' => 'send_prompt_button',
        'title' => get_string('tune', 'mod_valuemapdoc'),
        'aria-label' => get_string('tune', 'mod_valuemapdoc')
    ]
));

$mform->addElement('html', html_writer::end_div());

        // Koniec rzędu
$mform->addElement('html', html_writer::end_div());


        // Ukryte elementy (przekazywane w sesji lub do zapisu)
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'docid');
        $mform->setType('docid', PARAM_INT);
        $mform->setDefault('docid', $customdata['docid']);


        $mform->addElement('hidden', 'tunedresult');
        $mform->setType('tunedresult', PARAM_RAW);
        $mform->setDefault('tunedresult', $customdata['workingcontent']);


        // Przyciski zapisujące i anulujące

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'editcontent', get_string('edit', 'mod_valuemapdoc'),
            ['class' => 'btn btn-primary', 'id' => 'editbutton']
        );
        $buttonarray[] = $mform->createElement('submit', 'savecontent', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);


    }

}