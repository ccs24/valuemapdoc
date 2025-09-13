<?php
namespace mod_valuemapdoc\local;

defined('MOODLE_INTERNAL') || die();

class storage {
    
    public static function  save_content_as_localfile($docid) {
        global $DB, $USER;
        // Pobierz treść dokumentu.
        $document = $DB->get_record('valuemapdoc_content', ['id' => $docid], '*', MUST_EXIST);
        $content = $document->content;
        $templateid = $document->templateid;

        // Przygotuj nazwę pliku na podstawie opportunity name + timestamp
        $cleansafe = clean_param($document->opportunityname, PARAM_FILE);
        $timestamp = date('Ymd_His');
        $filename = "{$cleansafe}_{$timestamp}.html";

        // Zapisz jako plik i wyślij do pobrania
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $content;
        exit;
    }

    public static function  save_document_as_file($docid, $cm) {
        global $DB, $USER;

        $moduleinstance = $DB->get_record('valuemapdoc', ['id' => $cm->instance], '*', MUST_EXIST);
        $targetcmid = $moduleinstance->targetactivity;

        // Pobierz treść dokumentu.
        $document = $DB->get_record('valuemapdoc_content', ['id' => $docid], '*', MUST_EXIST);
        $content = $document->content;
        $templateid = $document->templateid;

        // Przygotuj nazwę pliku na podstawie opportunity name + timestamp
        $cleansafe = clean_param($document->opportunityname, PARAM_FILE);
        $timestamp = date('Ymd_His');
        $filename = "{$cleansafe}_{$timestamp}.txt";


        if ($targetcmid == 0) {
            $filepath = self::save_to_private_files(
                $USER->id,
                $moduleinstance->name,
                $filename,
                $content
            );
        } else {
            $filepath = self::save_to_student_folder(
                $targetcmid,
                $USER->id,
                $moduleinstance->name . "-" . $filename,
                $content
            );
        }
        

        // Zapisz plik lokalnie
        return  $filepath;
    }

    /**
     * Zapisuje plik do prywatnych plików użytkownika.
     *
     * @param int $userid ID użytkownika
     * @param string $opportunity Nazwa szansy
     * @param string $filename Nazwa pliku
     * @param string $content Treść pliku
     * @return string|null Ścieżka do pliku lub null w przypadku błędu
     */
    
    public static function save_to_private_files(int $userid, string $opportunity, string $filename, string $content): ?string {
        $fs = get_file_storage();
    
        $context = \context_user::instance($userid);
        $component = 'user';
        $filearea = 'private';
        $itemid = 0;
        $safeopportunity = clean_param($opportunity, PARAM_FILE);
        $filepath = '/' . trim($safeopportunity, '/') . '/';
    
        // Usuń plik jeśli już istnieje
        if ($existing = $fs->get_file($context->id, $component, $filearea, $itemid, $filepath, $filename)) {
            $existing->delete();
        }
    
        // Zapisz nowy plik
        $file = $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => $component,
            'filearea' => $filearea,
            'itemid' => $itemid,
            'filepath' => $filepath,
            'filename' => $filename,
            'userid' => $userid,
        ], $content);
    
        return $file ? $file->get_filepath() . $file->get_filename() : null;
    }

    /**
     * Zapisuje plik do folderu studenta w module publication.
     *
     * @param int $publicationcmid ID kursu
     * @param int $userid ID użytkownika
     * @param string $opportunityname Nazwa szansy
     * @param string $content Treść pliku
     * @return string|null Ścieżka do pliku lub null w przypadku błędu
     */
    public static function save_to_student_folder(int $publicationcmid, int $userid, string $opportunityname, string $content): ?string {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/mod/publication/locallib.php');

    
        $fs = get_file_storage();
    
        // Pobierz moduł publication.
        $cm = get_coursemodule_from_id('publication', $publicationcmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $publication = $DB->get_record('publication', ['id' => $cm->instance], '*', MUST_EXIST);

        // Sprawdź tryb grup i przynależność użytkownika
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode == SEPARATEGROUPS) {
            $groups = groups_get_user_groups($cm->course, $userid);
            $usergroups = $groups[0];
            $currentgroup = groups_get_activity_group($cm, true);
            $accessallgroups = has_capability('moodle/site:accessallgroups', $context, $userid);
            if (!$accessallgroups && (!in_array($currentgroup, $usergroups))) {
                throw new \required_capability_exception($context, 'moodle/site:accessallgroups', 'nopermissions', '');
            }
        }
    
        $component = 'mod_publication';
        $filearea = 'attachment'; // dokładnie tak, jak w upload_form.php
        $itemid = $userid;
        $filepath = '/';
    
        // Przygotuj nazwę pliku na podstawie opportunity name + timestamp
        $cleansafe = clean_param($opportunityname, PARAM_FILE);
        $timestamp = date('Ymd_His');
        $filename = "{$cleansafe}_{$timestamp}.txt";
    
        // Usuń poprzedni plik o tej nazwie (jeśli istnieje)
        if ($existing = $fs->get_file($context->id, $component, $filearea, $itemid, $filepath, $filename)) {
            $existing->delete();
        }
    
        // Stwórz plik w przestrzeni studenta
        $file = $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => $component,
            'filearea'  => $filearea,
            'itemid'    => $itemid,
            'filepath'  => $filepath,
            'filename'  => $filename,
            'userid'    => $userid
        ], $content);
    
        // Zapisz wpis w tabeli publication_file
        $record = (object)[
            'publication' => $publication->id,
            'userid' => $userid,
            'timecreated' => time(),
            'fileid' => $file->get_id(),
            'studentapproval' => 0,
            'teacherapproval' => 0,
            'filename' => $file->get_filename(),
            'type' => PUBLICATION_MODE_UPLOAD
        ];
    
        $record->id = $DB->insert_record('publication_file', $record);
    
        return $file ? $file->get_filepath() . $file->get_filename() : null;
    }

    
    public static function save_to_local_file(int $userid, string $opportunityname, string $filename, string $content): ?string {
        global $CFG;
    
        // Zbuduj ścieżkę do katalogu
        $safeopportunity = clean_param($opportunityname, PARAM_FILE);
        $safefilename = clean_param($filename, PARAM_FILE);
        $timestamp = date('Ymd_His');
        $subdir = "valuemap_exports/{$userid}/{$safeopportunity}";
    
        $fullpath = "{$CFG->dataroot}/{$subdir}";
        if (!file_exists($fullpath)) {
            if (!mkdir($fullpath, 0777, true)) {
                debugging("Could not create directory: $fullpath");
                return null;
            }
        }
    
        $filepath = "{$fullpath}/{$timestamp}_{$safefilename}";
        if (file_put_contents($filepath, $content) === false) {
            debugging("Could not write to file: $filepath");
            return null;
        }
    
        return $filepath;
    }

    /**
     * Zapisuje plik do zadania assign użytkownika.
     *
     * @param int $assigncmid ID modułu zadania assign
     * @param int $userid ID użytkownika
     * @param string $filename Nazwa pliku
     * @param string $content Treść pliku
     * @return string|null Ścieżka do pliku lub null w przypadku błędu
     */
    public static function save_to_assign_submission(int $assigncmid, int $userid, string $filename, string $content): ?string {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $fs = get_file_storage();

        $cm = get_coursemodule_from_id('assign', $assigncmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        // Sprawdź tryb grup i przynależność użytkownika
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode == SEPARATEGROUPS) {
            $groups = groups_get_user_groups($cm->course, $userid);
            $usergroups = $groups[0];
            $currentgroup = groups_get_activity_group($cm, true);
            $accessallgroups = has_capability('moodle/site:accessallgroups', $context, $userid);
            if (!$accessallgroups && (!in_array($currentgroup, $usergroups))) {
                throw new \required_capability_exception($context, 'moodle/site:accessallgroups', 'nopermissions', '');
            }
        }

        $assign = new \assign($context, false, false);

        // Znajdź lub utwórz submission użytkownika.
        $submission = $assign->get_user_submission($userid, true);

        $component = 'assignsubmission_file';
        $filearea = 'submission_files';
        $itemid = $submission->id;
        $filepath = '/';

        // Usuń istniejący plik jeśli już istnieje
        if ($existing = $fs->get_file($context->id, $component, $filearea, $itemid, $filepath, $filename)) {
            $existing->delete();
        }

        // Utwórz plik
        $file = $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => $component,
            'filearea' => $filearea,
            'itemid' => $itemid,
            'filepath' => $filepath,
            'filename' => $filename,
            'userid' => $userid,
        ], $content);

        // Oznacz submission jako wysłane.
        $submission->status = 'submitted';
        $submission->timemodified = time();
        $DB->update_record('assign_submission', $submission);

        return $file ? $file->get_filepath() . $file->get_filename() : null;
    }

    /**
     * Pobiera listę dostępnych zadań assign w kursie, do których użytkownik ma dostęp.
     *
     * @param int $courseid ID kursu
     * @param int $userid ID użytkownika
     * @return array Lista assignów w formacie assignid => nazwazadania
     */
    public static function get_user_assignments(int $courseid, int $userid): array {
        global $DB;

        $assignments = [];
        $sql = "SELECT a.id, a.name
                  FROM {assign} a
                  JOIN {course_modules} cm ON cm.instance = a.id
                  JOIN {modules} m ON m.id = cm.module
                  JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextlevel
             LEFT JOIN {role_assignments} ra ON ra.contextid = ctx.id AND ra.userid = :userid
                 WHERE a.course = :courseid
                   AND m.name = :modulename
                   AND cm.visible = 1";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
            'courseid' => $courseid,
            'modulename' => 'assign'
        ];

        $records = $DB->get_records_sql($sql, $params);

        // Filtruj zadania według grup, jeśli tryb grup to SEPARATEGROUPS i użytkownik nie ma accessallgroups
        $filteredassignments = [];
        foreach ($records as $record) {
            $cm = get_coursemodule_from_instance('assign', $record->id, $courseid, false, MUST_EXIST);
            $context = \context_module::instance($cm->id);
            $groupmode = groups_get_activity_groupmode($cm);
            $accessallgroups = has_capability('moodle/site:accessallgroups', $context, $userid);
            if ($groupmode == SEPARATEGROUPS && !$accessallgroups) {
                $groups = groups_get_user_groups($courseid, $userid);
                $usergroups = $groups[0];
                $currentgroup = groups_get_activity_group($cm, true);
                if (!in_array($currentgroup, $usergroups)) {
                    continue;
                }
            }
            $filteredassignments[$record->id] = $record->name;
        }

        return $filteredassignments;
    }
}
