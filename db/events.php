<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\\mod_valuemapdoc\\event\\record_added',
        'callback'    => '\\mod_valuemapdoc\\observer::record_added',
    ],
    [
        'eventname'   => '\\mod_valuemapdoc\\event\\record_viewed',
        'callback'    => '\\mod_valuemapdoc\\observer::record_viewed',
    ],
    [
        'eventname'   => '\\mod_valuemapdoc\\event\\document_generated',
        'callback'    => '\\mod_valuemapdoc\\observer::document_generated',
    ],
    [
        'eventname'   => '\\mod_valuemapdoc\\event\\document_saved',
        'callback'    => '\\mod_valuemapdoc\\observer::document_saved',
    ],
    [
        'eventname'   => '\\mod_valuemapdoc\\event\\document_rated',
        'callback'    => '\\mod_valuemapdoc\\observer::document_rated',
    ],
];
