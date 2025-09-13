<?php 

namespace mod_valuemapdoc;

defined('MOODLE_INTERNAL') || die();

class template_helper {
    public static function get_template_by_id(int $templateid): ?\stdClass {
        global $DB;
        return $DB->get_record('valuemapdoc_templates', ['id' => $templateid], '*', IGNORE_MISSING);
    }

    public static function get_template_fields_by_id(int $templateid): ?array {
        global $DB;
        $record = $DB->get_record('valuemapdoc_templates', ['id' => $templateid], 'name, templatetype', IGNORE_MISSING);
        if ($record) {
            return [
                'name' => $record->name,
                'templatetype' => $record->templatetype
            ];
        }
        return null;
    }

    public static function get_template_body_by_id(int $templateid): ?string {
        global $DB;
        $record = $DB->get_record('valuemapdoc_templates', ['id' => $templateid], 'templatebody', IGNORE_MISSING);
        if ($record) {
            return $record->templatebody;
        }
        return null;
    }

    public static function get_template_prompt_by_id(int $templateid): ?string {
        global $DB;
        $record = $DB->get_record('valuemapdoc_templates', ['id' => $templateid], 'prompt', IGNORE_MISSING);
        if ($record) {
            return $record->prompt;
        }
        return null;
    }

    public static function get_templates_list(): array {
        global $DB;
        // Prawidłowe zapytanie z DISTINCT
        $sql = "SELECT DISTINCT templatetype FROM {valuemapdoc_templates} ORDER BY templatetype";
        $records = $DB->get_fieldset_sql($sql);
    
        if (empty($records)) {
            return [];
        }
    
        // Buduj tablicę do selecta
        $options = [];
        foreach ($records as $templatetype) {
            $options[$templatetype] = $templatetype;
        }
        return $options;
    }
}