<?php
namespace mod_valuemapdoc\local;

require_once($CFG->libdir . '/filelib.php');
use core\http_client;
use moodle_exception;

class openai_client {


    /**
     * Generates text using the OpenAI API with optional parameters for temperature, top_p, penalties, and message history.
     *
     * @param string $content User's message content.
     * @param string $systemprompt System prompt for the assistant.
     * @param float $temperature Sampling temperature to use (default 0.7).
     * @param float $top_p Nucleus sampling probability (default 1.0).
     * @param float $frequency_penalty Frequency penalty (default 0.0).
     * @param float $presence_penalty Presence penalty (default 0.0).
     * @param array $history Optional message history (default empty array). If provided, replaces default messages.
     * @return string|null The generated content, or null on failure.
     */
    public static function generate_text2(
        string $content,
        string $systemprompt = "",
        float $temperature = 0.7,
        float $top_p = 1.0,
        float $frequency_penalty = 0.0,
        float $presence_penalty = 0.0,
        array $history = []
    ): ?string {
        global $CFG;


        $model = get_config('mod_valuemapdoc', 'openai_model') ?? 'gpt-4';
        $apikey = get_config('mod_valuemapdoc', 'openai_apikey');


        $curlbody = [
            "model" => $model,
            "temperature" => $temperature,
            "top_p" => $top_p,
            "frequency_penalty" => $frequency_penalty,
            "presence_penalty" => $presence_penalty,
        ];
        if (!empty($history)) {
            $curlbody['messages'] = $history;
        } else {
            $curlbody['messages'] = [
                [ 'role' => 'system', 'content' => $systemprompt ],
                [ 'role' => 'user', 'content' => $content ]
            ];
        }

        $curl = new \curl();
        $curl->setopt(array(
            'CURLOPT_HTTPHEADER' => array(
                'Authorization: Bearer ' . $apikey,
                'Content-Type: application/json'
            ),
        ));

        
        try {
            $response = $curl->post("https://api.openai.com/v1/chat/completions", json_encode($curlbody));

            $data = json_decode($response, true);

            if (!isset($data['choices'][0]['message']['content'])) {
                debugging("[OpenAI] Invalid response structure: " . $response->getContent(), DEBUG_DEVELOPER);
                return null;
            }

            return $data['choices'][0]['message']['content'];
        } catch (\Exception $e) {
            debugging("[OpenAI] Request failed: " . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }

    }


    public static function generate_text(string $content, string $systemprompt): ?string {
        global $CFG;

        $apikey = get_config('mod_valuemapdoc', 'openai_apikey');
        $model = get_config('mod_valuemapdoc', 'openai_model') ?? 'gpt-4';

        debugging('[DEBUG] OpenAI API key (first 10 chars): ' . substr($apikey, 0, 10), DEBUG_DEVELOPER);

        if (empty($apikey)) {
            debugging("[OpenAI] API key not set", DEBUG_DEVELOPER);
            return null;
        }

        $httpclient = new http_client();

        $url = 'https://api.openai.com/v1/chat/completions';

        $request = [
            'model' => $model,
            'messages' => [
                [ 'role' => 'system', 'content' => $systemprompt ],
                [ 'role' => 'user', 'content' => $content ]
            ],
            'temperature' => 0.7
        ];
        

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apikey
        ];

//        var_dump($headers); die();

        try {
            $response = $httpclient->post($url, [
                'headers' => $headers,
                'timeout' => 30,
                'body' => json_encode($request)
            ]);


            $data = json_decode($response->getContent(), true);

            if (!isset($data['choices'][0]['message']['content'])) {
                debugging("[OpenAI] Invalid response structure: " . $response->getContent(), DEBUG_DEVELOPER);
                return null;
            }

            return $data['choices'][0]['message']['content'];
        } catch (\Exception $e) {
            debugging("[OpenAI] Request failed: " . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }

    /**
     * Generate text using OpenAI API with timeout support
     * @param string $prompt
     * @param string $system_prompt
     * @param array $options Additional options (timeout, max_tokens, etc.)
     * @return string
     */
    public static function generate_text3($prompt, $system_prompt = '', $options = []) {
        // Default options
        $default_options = [
            'timeout' => 120,
            'max_tokens' => 4000,
            'temperature' => 0.7
        ];
        
        $options = array_merge($default_options, $options);
        
        $model = get_config('mod_valuemapdoc', 'openai_model') ?? 'gpt-4';
        $api_key = get_config('mod_valuemapdoc', 'openai_apikey');

        if (empty($api_key)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $url = 'https://api.openai.com/v1/chat/completions';
        
        $messages = [];
        if (!empty($system_prompt)) {
            $messages[] = [
                'role' => 'system',
                'content' => $system_prompt
            ];
        }
        
        $messages[] = [
            'role' => 'user', 
            'content' => $prompt
        ];

        $data = [
            'model' => $model, //get_config('mod_valuemapdoc', 'openai_model') ?: 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'],
            'temperature' => $options['temperature']
        ];

        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key
            ],
            CURLOPT_TIMEOUT => $options['timeout'], // Request timeout
            CURLOPT_CONNECTTIMEOUT => 30, // Connection timeout
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $curl_options);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        
        curl_close($curl);

        if ($curl_error) {
            throw new \Exception('cURL Error: ' . $curl_error);
        }

        if ($http_code !== 200) {
            $error_data = json_decode($response, true);
            $error_message = $error_data['error']['message'] ?? 'HTTP Error ' . $http_code;
            throw new \Exception('OpenAI API Error: ' . $error_message);
        }

        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
        }

        if (!isset($decoded['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid API response structure');
        }

        return trim($decoded['choices'][0]['message']['content']);
    }
}
