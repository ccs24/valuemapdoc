<?php
namespace mod_valuemapdoc;

defined('MOODLE_INTERNAL') || die();

class observer {
    public static function record_added($event) {
       // debugging("Observer: record_added triggered for user {$event->userid}", DEBUG_DEVELOPER);
    }

    public static function record_viewed($event) {
        //debugging("Observer: record_viewed triggered for user {$event->userid}", DEBUG_DEVELOPER);
    }

    public static function document_generated($event) {
        // debugging("Observer: document_generated triggered for user {$event->userid}", DEBUG_DEVELOPER);
    }

    public static function document_saved($event) {
        // debugging("Observer: document_saved triggered for user {$event->userid}", DEBUG_DEVELOPER);
    }

    public static function document_rated($event) {
        // debugging("Observer: document_rated triggered for user {$event->userid}", DEBUG_DEVELOPER);
    }
}
