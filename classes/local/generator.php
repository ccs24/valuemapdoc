<?php
namespace mod_valuemapdoc\local;


defined('MOODLE_INTERNAL') || die();


class generator {

    /**
     * Generuje treść dokumentu na podstawie szablonu i danych wpisów.
     *
     * @param array $entries Dane z Mapy Wartości (tablica obiektów stdClass)
     * @param string $template Treść szablonu z polami w stylu {pole}
     * @return string Wygenerowany tekst dokumentu
     */
    public static function generate_document(array $entries, string $template): string {
        $combineddata = [];

        foreach ($entries as $entry) {
            foreach ($entry as $key => $value) {
                if (!isset($combineddata[$key])) {
                    $combineddata[$key] = [];
                }
                $combineddata[$key][] = trim($value ?? '');
            }
        }

        // Tworzenie mapy: {pole} => "połączone wartości"
        $replacements = [];
        foreach ($combineddata as $key => $values) {
            $placeholder = '[' . $key . ']';
            $replacements[$placeholder] = implode("; ", array_unique($values));
        }

        // Podstawianie danych do szablonu
        $content = strtr($template, $replacements);


        return $content;
    }

     /**
     * Formatuje dane z Value Map do postaci klucz = wartość
     * @param array $entries
     * @return string
     */
    public static function format_entries_for_prompt(array $entries): string {
        $output = [];
        $counter = 1;

        // Opakuj tablicę w klucz "records"
        $data = ['records' => array_values($entries)]; // array_values() usuwa klucze ID jako indeksy
        // 
        // Zakoduj jako JSON
        $json = "Mapa Wartości w formacie JSON:\n\n" . json_encode($data, JSON_PRETTY_PRINT);
        //$json = json_encode($entries, JSON_PRETTY_PRINT);
        return $json;

//        var_dump($json); die();
/*
        foreach ($entries as $entry) {
            $lines = [];
            $lines[] = "=== Rekord {$counter} ===";
            $lines[] = "market = {$entry->market}";
            $lines[] = "industry = {$entry->industry}";
            $lines[] = "role = {$entry->role}";
            $lines[] = "businessgoal = {$entry->businessgoal}";
            $lines[] = "strategy = {$entry->strategy}";
            $lines[] = "difficulty = {$entry->difficulty}";
            $lines[] = "situation = {$entry->situation}";
            $lines[] = "statusquo = {$entry->statusquo}";
            $lines[] = "coi = {$entry->coi}";
            $lines[] = "impact = {$entry->impact}";
            $lines[] = "differentiator = {$entry->differentiator}";
            $lines[] = "newstate = {$entry->newstate}";
            $lines[] = "successmetric = {$entry->successmetric}";
            $lines[] = "impactstrategy = {$entry->impactstrategy}";
            $lines[] = "impactbusinessgoal = {$entry->impactbusinessgoal}";
            $lines[] = "impactothers = {$entry->impactothers}";
            $lines[] = "proof = {$entry->proof}";
            $lines[] = "time2results = {$entry->time2results}";
            $lines[] = "quote = {$entry->quote}";
            $lines[] = "clientname = {$entry->clientname}";
            $output[] = implode("\n", $lines);
            $counter++;
        }

        return implode("\n\n", $output);
        */
    }

    /**
     * Wczytuje treść szablonu z bazy danych.
     *
     * @param int $templateid ID szablonu
     * @return string Treść szablonu markdown
     */
    public static function load_template_by_id(int $templateid): \stdClass {
        global $DB;
//        $template = $DB->get_record('valuemapdoc_templates', ['id' => $templateid], 'templatebody', IGNORE_MISSING);
        $template = $DB->get_record('valuemapdoc_templates', ['id' => $templateid], '*', IGNORE_MISSING);
        return $template;// ? $template->templatebody : '';
    }
}


