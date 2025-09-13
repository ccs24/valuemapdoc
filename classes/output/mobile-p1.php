<?php
namespace mod_valuemapdoc\output;

defined('MOODLE_INTERNAL') || die();

class mobile {
    
    public static function mobile_course_view($args) {
        global $DB, $USER;
        
        error_log('VALUEMAPDOC MOBILE: Starting method with args: ' . json_encode($args));
        
        try {
            $cmid = $args['cmid'];
            
            // Pobierz podstawowe dane modułu
            $cm = get_coursemodule_from_id('valuemapdoc', $cmid, 0, false, MUST_EXIST);
            $context = \context_module::instance($cm->id);
            
            // Sprawdź uprawnienia
            require_capability('mod/valuemapdoc:view', $context);
            
            // Pobierz instancję modułu
            $valuemapdoc = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
            
            // Pobierz wpisy mapy wartości dla tego modułu
            $entries = $DB->get_records('valuemapdoc_entries', ['cid' => $cm->id], 'timemodified DESC');
            
            error_log('VALUEMAPDOC MOBILE: Found ' . count($entries) . ' entries');
            
            // Przygotuj dane dla template
            $data = [
                'modulename' => format_string($valuemapdoc->name),
                'intro' => format_module_intro('valuemapdoc', $valuemapdoc, $cm->id),
                'cmid' => $cmid,
                'courseid' => $cm->course,
                'canview' => has_capability('mod/valuemapdoc:view', $context),
                'entries_count' => count($entries),
                'has_entries' => !empty($entries),
                'entries' => []
            ];
            
            // Dodaj pierwsze 5 wpisów (dla testu)
            $counter = 0;
            foreach ($entries as $entry) {
                if ($counter >= 5) break; // Limit dla aplikacji mobilnej
                
                $data['entries'][] = [
                    'id' => $entry->id,
                    'market' => format_string($entry->market ?? 'Not specified'),
                    'industry' => format_string($entry->industry ?? 'Not specified'),
                    'role' => format_string($entry->role ?? 'Not specified'),
                    'businessgoal' => format_text($entry->businessgoal ?? '', FORMAT_PLAIN),
                    'difficulty' => format_text($entry->difficulty ?? '', FORMAT_PLAIN),
                    'maturity' => $entry->maturity ?? 0,
                    'ismaster' => $entry->ismaster ?? 0,
                    'timemodified' => $entry->timemodified,
                    'timemodified_formatted' => userdate($entry->timemodified, get_string('strftimedatetimeshort')),
                    'can_edit' => ($entry->userid == $USER->id)
                ];
                $counter++;
            }
            
            error_log('VALUEMAPDOC MOBILE: Prepared data for ' . count($data['entries']) . ' entries');
            
            $html = self::generate_mobile_html($data);
            
            return [
                'templates' => [
                    [
                        'id' => 'main',
                        'html' => $html,
                    ],
                ],
                'javascript' => self::get_mobile_javascript(),
                'otherdata' => [
                    'cmid' => $cmid,
                    'entries_count' => $data['entries_count']
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
     * Generate HTML for mobile view
     */
    private static function generate_mobile_html($data) {
        $html = '<div class="valuemap-mobile" style="padding: 16px;">';
        
        // Header
        $html .= '<div class="valuemap-header" style="margin-bottom: 20px;">';
        $html .= '<h2 style="margin: 0; color: #1f2937;">' . $data['modulename'] . '</h2>';
        if (!empty($data['intro'])) {
            $html .= '<div style="margin-top: 12px; padding: 12px; background: #f3f4f6; border-radius: 8px;">';
            $html .= $data['intro'];
            $html .= '</div>';
        }
        $html .= '</div>';
        
        // Stats
        $html .= '<div class="stats" style="margin-bottom: 20px; padding: 12px; background: #dbeafe; border-radius: 8px;">';
        $html .= '<p style="margin: 0; font-weight: bold; color: #1e40af;">📊 Total entries: ' . $data['entries_count'] . '</p>';
        $html .= '</div>';
        
        // Entries
        if ($data['has_entries']) {
            $html .= '<div class="entries-section">';
            $html .= '<h3 style="color: #374151; margin-bottom: 16px;">📋 Recent Entries</h3>';
            
            foreach ($data['entries'] as $entry) {
                $html .= '<div class="entry-card" style="
                    margin-bottom: 16px; 
                    padding: 16px; 
                    background: white; 
                    border: 1px solid #e5e7eb; 
                    border-radius: 8px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                ">';
                
                // Header
                $html .= '<div style="margin-bottom: 12px;">';
                $html .= '<h4 style="margin: 0; color: #1f2937;">' . $entry['market'] . ' - ' . $entry['industry'] . '</h4>';
                if (!empty($entry['role'])) {
                    $html .= '<p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">Role: ' . $entry['role'] . '</p>';
                }
                $html .= '</div>';
                
                // Content
                if (!empty($entry['businessgoal'])) {
                    $html .= '<div style="margin-bottom: 8px;">';
                    $html .= '<strong style="color: #059669;">Business Goal:</strong>';
                    $html .= '<p style="margin: 4px 0 0 0; font-size: 14px;">' . substr($entry['businessgoal'], 0, 150) . '...</p>';
                    $html .= '</div>';
                }
                
                if (!empty($entry['difficulty'])) {
                    $html .= '<div style="margin-bottom: 8px;">';
                    $html .= '<strong style="color: #dc2626;">Difficulty:</strong>';
                    $html .= '<p style="margin: 4px 0 0 0; font-size: 14px;">' . substr($entry['difficulty'], 0, 150) . '...</p>';
                    $html .= '</div>';
                }
                
                // Footer
                $html .= '<div style="margin-top: 12px; padding-top: 8px; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">';
                $html .= '<small style="color: #6b7280;">Modified: ' . $entry['timemodified_formatted'] . '</small>';
                
                $badges = '';
                if ($entry['maturity'] > 0) {
                    $badges .= '<span style="background: #10b981; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 4px;">Maturity: ' . $entry['maturity'] . '</span>';
                }
                if ($entry['ismaster']) {
                    $badges .= '<span style="background: #3b82f6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 4px;">Master</span>';
                }
                $html .= '<div>' . $badges . '</div>';
                
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<div class="no-entries" style="text-align: center; padding: 40px 20px; color: #6b7280;">';
            $html .= '<h3>📝 No entries yet</h3>';
            $html .= '<p>Start by adding your first value map entry on the website.</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * JavaScript for mobile interactions
     */
    private static function get_mobile_javascript() {
        return '
            console.log("ValueMapDoc mobile view loaded");
            
            // Refresh functionality
            this.refreshContent = function() {
                console.log("Refreshing ValueMapDoc content");
                // Implement refresh logic if needed
            };
        ';
    }
}