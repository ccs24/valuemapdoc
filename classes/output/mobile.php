<?php
namespace mod_valuemapdoc\output;

defined('MOODLE_INTERNAL') || die();

class mobile {
    
    /**
     * Returns the mobile view for the valuemapdoc module
     * 
     * @param array $args Arguments from mobile app
     * @return array
     */
    public static function mobile_course_view($args) {
        global $OUTPUT, $DB, $USER;

        error_log('Mobile method reached! Args: ' . print_r($args, true));
        
        try {
            $cmid = $args['cmid'];
            $courseid = $args['courseid'] ?? 0;
            
            // Pobierz course module
            $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);
            $context = \context_module::instance($cm->id);
            
            // Sprawdź uprawnienia
            require_capability('mod/valuemapdoc:view', $context);

            // Pobierz dane aktywności
            $valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
            
            // Pobierz wpisy mapy wartości dla tego modułu
            $valuemap_entries = $DB->get_records('valuemapdoc_entries', ['cid' => $valuemapdoc->id]);
            error_log('VALUEMAPDOC MOBILE: Found ' . count($valuemap_entries) . ' entries');
                
            // Przygotuj dane dla template
            $data = new \stdClass();
            $data->intro = format_module_intro('valuemapdoc', $valuemapdoc, $cm->id);
            $data->name = $valuemapdoc->name;
            $data->cmid = $cmid;
            $data->courseid = $courseid;
            $data->userid = $USER->id;
            $data->canview = has_capability('mod/valuemapdoc:view', $context);
            $data->canedit = has_capability('mod/valuemapdoc:addinstance', $context);
            
            // Przekształć wpisy mapy wartości dla widoku mobilnego
            $data->valuemap_entries = [];
            foreach ($valuemap_entries as $entry) {
                $data->valuemap_entries[] = [
                    'id' => $entry->id,
                    'market' => format_string($entry->market ?? ''),
                    'industry' => format_string($entry->industry ?? ''),
                    'role' => format_string($entry->role ?? ''),
                    'businessgoal' => format_text($entry->businessgoal ?? '', FORMAT_PLAIN),
                    'strategy' => format_text($entry->strategy ?? '', FORMAT_PLAIN),
                    'difficulty' => format_text($entry->difficulty ?? '', FORMAT_PLAIN),
                    'situation' => format_text($entry->situation ?? '', FORMAT_PLAIN),
                    'statusquo' => format_text($entry->statusquo ?? '', FORMAT_PLAIN),
                    'coi' => format_text($entry->coi ?? '', FORMAT_PLAIN), // Cost of inaction
                    'differentiator' => format_text($entry->differentiator ?? '', FORMAT_PLAIN),
                    'impact' => format_text($entry->impact ?? '', FORMAT_PLAIN),
                    'newstate' => format_text($entry->newstate ?? '', FORMAT_PLAIN),
                    'successmetric' => format_text($entry->successmetric ?? '', FORMAT_PLAIN),
                    'proof' => format_text($entry->proof ?? '', FORMAT_PLAIN),
                    'time2results' => format_string($entry->time2results ?? ''),
                    'quote' => format_text($entry->quote ?? '', FORMAT_PLAIN),
                    'clientname' => format_string($entry->clientname ?? ''),
                    'maturity' => $entry->maturity ?? 0,
                    'ismaster' => $entry->ismaster ?? 0,
                    'timemodified' => $entry->timemodified,
                    'timemodified_formatted' => userdate($entry->timemodified, get_string('strftimedatetimeshort')),
                    'can_edit' => ($entry->userid == $USER->id || $data->canedit),
                ];
            }
            
            // POPRAWKA: Używaj $data->valuemap_entries zamiast $data['entries']
            error_log('VALUEMAPDOC MOBILE: Prepared data for ' . count($data->valuemap_entries) . ' entries');
            $data->has_entries = !empty($data->valuemap_entries);

            return [
                'templates' => [
                    [
                        'id' => 'main',
                        'html' => $OUTPUT->render_from_template('mod_valuemapdoc/mobile_view', $data),
                    ],
                ],
                'javascript' => self::get_mobile_javascript(),
                'otherdata' => [
                    'cmid' => $cmid,
                    'courseid' => $courseid,
                ],
            ];
        } catch (Exception $e) {
            error_log('VALUEMAPDOC MOBILE ERROR: ' . $e->getMessage());
            
            return [
                'templates' => [
                    [
                        'id' => 'main',
                        'html' => '<div class="alert alert-danger">
                            <h4>Error loading Value Map</h4>
                            <p>' . $e->getMessage() . '</p>
                        </div>',
                    ],
                ],
                'javascript' => '',
            ];
        }
    }
    
    /**
     * Returns JavaScript for mobile view
     * 
     * @return string
     */
    private static function get_mobile_javascript() {
        return '
            var that = this;
            
            // Funkcja do odświeżania danych
            this.refreshContent = function() {
                console.log("Refreshing ValueMapDoc content");
                that.CoreCourseModuleDelegate.invalidateContent(that.module.id);
                that.doRefresh();
            };
            
            // Obsługa kliknięć w wpisy mapy
            this.openValueMapEntry = function(entryId) {
                // Otwórz szczegóły wpisu mapy wartości
                that.CoreNavigator.navigate("entry-detail", {
                    cmid: that.module.id,
                    entryid: entryId
                });
            };
            
            // Dodaj nowy wpis
            this.addNewEntry = function() {
                if (that.canEdit) {
                    that.CoreNavigator.navigate("entry-form", {
                        cmid: that.module.id,
                        action: "add"
                    });
                }
            };
        ';
    }
    
    /**
     * Offline function to get valuemap data
     * 
     * @param array $args
     * @return array
     */
    public static function mod_valuemapdoc_get_valuemap_data($args) {
        global $DB;
        
        $cmid = $args['cmid'];
        $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        require_capability('mod/valuemapdoc:view', $context);
        
        $valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
        $entries = $DB->get_records('valuemapdoc_entries', ['cid' => $valuemapdoc->id]);
        
        return [
            'valuemapdoc' => $valuemapdoc,
            'entries' => array_values($entries), // array_values dla lepszej serializacji JSON
        ];
    }
}