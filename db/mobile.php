<?php
defined('MOODLE_INTERNAL') || die();

$addons = [
    'mod_valuemapdoc' => [
        'handlers' => [
            'valuemapdoc' => [
                'delegate' => 'CoreCourseModuleDelegate',
                'method' => 'mobile_course_view', // Zgodne z klasą
                'displaydata' => [
                    'title' => get_string('pluginname', 'mod_valuemapdoc'), // Używaj language strings
                    'icon' => 'fas-map', // Lepszy wybór ikony dla mapy wartości
                    'class' => '', // Opcjonalne dodatkowe klasy CSS
                ],
                'offlinefunctions' => [
                    'mod_valuemapdoc_get_valuemap_data' => [], // Bardziej opisowa nazwa
                ],
                'styles' => [
                    'url' => $CFG->wwwroot . '/mod/valuemapdoc/mobile/styles_app.css',
                    'version' => 2024051400 // Wersja dla cache busting
                ],
            ],
        ],
        'lang' => [
            ['pluginname', 'mod_valuemapdoc'],
            ['modulename', 'mod_valuemapdoc'],
        ],
    ]
];