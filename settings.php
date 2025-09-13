<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Główna kategoria ustawień dla pluginu
    $ADMIN->add('modsettings', new admin_category('mod_valuemapdoc', get_string('pluginname', 'mod_valuemapdoc')));

    // Strona z ustawieniami OpenAI
    $settings = new admin_settingpage('modsettingvaluemapdoc', get_string('pluginname', 'mod_valuemapdoc'));

    $settings->add(new admin_setting_configtext(
        'mod_valuemapdoc/openai_apikey',
        get_string('openai_apikey', 'mod_valuemapdoc'),
        get_string('openai_apikey_desc', 'mod_valuemapdoc'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'mod_valuemapdoc/openai_model',
        get_string('openai_model', 'mod_valuemapdoc'),
        get_string('openai_model_desc', 'mod_valuemapdoc'),
        'gpt-4',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtextarea(
        'mod_valuemapdoc/default_system_prompt',
        get_string('default_system_prompt', 'mod_valuemapdoc'),
        get_string('default_system_prompt_desc', 'mod_valuemapdoc'),
        "You are a B2B complex sales expert. You generate sales content based on the Value Map."
    ));

    $settings->add(new admin_setting_configtextarea(
        'mod_valuemapdoc/default_template_prompt',
        get_string('default_template_prompt', 'mod_valuemapdoc'),
        get_string('default_template_prompt_desc', 'mod_valuemapdoc'),
        ""
    ));
  //  $ADMIN->add('mod_valuemapdoc', $settings);

    // Podstrona do zarządzania szablonami
    $ADMIN->add('mod_valuemapdoc', new admin_externalpage(
        'mod_valuemapdoc_templates',
        get_string('templatesadmin', 'mod_valuemapdoc'),
        new moodle_url('/mod/valuemapdoc/admin_templates.php'),
        'moodle/site:config'
    ));
}
