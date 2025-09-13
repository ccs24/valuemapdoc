<?php

namespace mod_valuemapdoc\local;

class openai_params_helper {
    private $params = [
        'temperature' => 0.7,
        'top_p' => 1.0,
        'frequency_penalty' => 0.0,
        'presence_penalty' => 0.0,
        'history' => [],
    ];

    public function __construct(?string $json = null) {
        if ($json) {
            $data = json_decode($json, true);
            if (is_array($data)) {
                $this->params = array_merge($this->params, $data);
            }
        }
    }

    public function get(string $key) {
        return $this->params[$key] ?? null;
    }

    public function set(string $key, $value): void {
        $this->params[$key] = $value;
    }

    public function to_json(): string {
        return json_encode($this->params, JSON_UNESCAPED_UNICODE);
    }

    public function to_array(): array {
        return $this->params;
    }

    /**
     * Adds OpenAI parameters fields to a Moodle form.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     */
    public static function add_to_mform(\MoodleQuickForm $mform): void {
        $mform->addElement('advcheckbox', 'enableopenaisettings', get_string('openai_settings', 'mod_valuemapdoc'), '', ['group' => 1], [0, 1]);
        $mform->addGroup([
            $mform->createElement('text', 'temperature', get_string('temperature', 'mod_valuemapdoc')),
            $mform->createElement('text', 'top_p', get_string('top_p', 'mod_valuemapdoc')),
            $mform->createElement('text', 'frequency_penalty', get_string('frequency_penalty', 'mod_valuemapdoc')),
            $mform->createElement('text', 'presence_penalty', get_string('presence_penalty', 'mod_valuemapdoc')),
        ], 'openai_params_group', get_string('openai_parameters', 'mod_valuemapdoc'), '', false);

        $mform->setDefault('temperature', 0.7);
        $mform->setDefault('top_p', 1.0);
        $mform->setDefault('frequency_penalty', 0.0);
        $mform->setDefault('presence_penalty', 0.0);

        $mform->setType('temperature', PARAM_FLOAT);
        $mform->setType('top_p', PARAM_FLOAT);
        $mform->setType('frequency_penalty', PARAM_FLOAT);
        $mform->setType('presence_penalty', PARAM_FLOAT);

        $mform->addRule('temperature', null, 'numeric', null, 'client');
        $mform->addRule('top_p', null, 'numeric', null, 'client');
        $mform->addRule('frequency_penalty', null, 'numeric', null, 'client');
        $mform->addRule('presence_penalty', null, 'numeric', null, 'client');
    }

    /*

    Przykład użycia:
    $mform = new \MoodleQuickForm();
    openai_params_helper::add_to_mform($mform);
    $mform->set_data($data); // $data to obiekt stdClass z danymi formularza
    $mform->setType('temperature', PARAM_FLOAT);
    $mform->setType('top_p', PARAM_FLOAT);

    $formdata = $mform->get_data();
    $paramshelper = openai_params_helper::from_formdata($formdata);
    
    $temperature = $paramshelper->get('temperature');
    $top_p = $paramshelper->get('top_p');
*/
    /**
     * Create an instance from form data.
     *
     * @param \stdClass $formdata
     * @return openai_params_helper
     */
    public static function from_formdata(\stdClass $formdata): openai_params_helper {
        $params = [
            'temperature' => (float)($formdata->temperature ?? 0.7),
            'top_p' => (float)($formdata->top_p ?? 1.0),
            'frequency_penalty' => (float)($formdata->frequency_penalty ?? 0.0),
            'presence_penalty' => (float)($formdata->presence_penalty ?? 0.0),
            'history' => [], // History will remain empty by default
        ];
        return new self(json_encode($params));
    }

    /**
     * Save the parameters as JSON into a database record.
     *
     * @param string $tablename
     * @param int $recordid
     * @param string $fieldname
     * @return void
     */
    public function save_to_database(string $tablename, int $recordid, string $fieldname = 'paramsjson'): void {
        global $DB;
        $record = new \stdClass();
        $record->id = $recordid;
        $record->{$fieldname} = $this->to_json();
        $DB->update_record($tablename, $record);
    }
}