<?php

$functions = [
    'mod_valuemapdoc_get_entries' => [
        'classname'   => 'mod_valuemapdoc\external\get_entries',
        'methodname'  => 'execute',
        'description' => 'Pobiera wpisy z mapy wartości dla danego kursu.',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> 'mod/valuemapdoc:view',
    ],
    'mod_valuemapdoc_delete_entry' => [
        'classname'   => 'mod_valuemapdoc\external\delete_entry',
        'methodname'  => 'execute',
        'description' => 'Usuwa wpis z mapy wartości na podstawie cmid i entryid.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'mod/valuemapdoc:view',
    ],
    'mod_valuemapdoc_delete_entry_bulk' => [
        'classname'   => 'mod_valuemapdoc\external\delete_entry_bulk',
        'methodname'  => 'execute',
        'description' => 'Usuwa wiele wpisów z mapy wartości na podstawie ich ID.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'mod/valuemapdoc:manageentries',
    ],
    'mod_valuemapdoc_get_master_instances' => [
        'classname'   => 'mod_valuemapdoc\external\get_master_instances',
        'methodname'  => 'execute',
        'description' => 'Zwraca listę instancji valuemapdoc oznaczonych jako master dla danego kursu',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> 'mod/valuemapdoc:view'
    ],
    'mod_valuemapdoc_tune_content_api' => [
        'classname' => 'mod_valuemapdoc\external\tune_content_api',
        'methodname' => 'execute',
        'description' => 'Tunes text with a prompt',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/valuemapdoc:generate'
    ],
    'mod_valuemapdoc_get_content_entries' => [
        'classname'   => 'mod_valuemapdoc\external\get_content_entries',
        'methodname'  => 'execute',
        'classpath'   => '', // zostaw pusty jeśli używasz namespacingu i autoload
        'description' => 'Pobiera wpisy treści zapisanych dokumentów',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'mod/valuemapdoc:view',
    ],
    'mod_valuemapdoc_delete_content' => [
        'classname'   => 'mod_valuemapdoc\external\delete_content',
        'methodname'  => 'execute',
        'classpath'   => '', // Moodle 4.x używa autoloadingu
        'description' => 'Delete a content entry from valuemapdoc module',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'mod/valuemapdoc:edit',
    ],
    'mod_valuemapdoc_update_cell' => [
        'classname'   => 'mod_valuemapdoc\external\update_cell',
        'methodname'  => 'execute',
        'classpath'   => '', // Moodle 4+ nie używa już tego
        'description' => 'Aktualizuje jedno pole rekordu ValueMapDoc',
        'type'        => 'write',
        'ajax'        => true, // umożliwia wywołanie przez requireJS (JS frontend)
        'capabilities'=> 'mod/valuemapdoc:edit',
    ],
    'mod_valuemapdoc_delete_bulk' => [
        'classname'   => 'mod_valuemapdoc\external\delete_entry_bulk',
        'methodname'  => 'execute',
        'description' => 'Usuwa wiele wpisów z mapy wartości na podstawie ich ID.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'mod/valuemapdoc:manageentries',
    ],
    'mod_valuemapdoc_get_mobile_content' => [
        'classname'   => 'mod_valuemapdoc\external\get_mobile_content',
        'methodname'  => 'execute',
        'description' => 'Returns basic content for the mobile app',
        'type'        => 'read',
        'capabilities' => '',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_valuemapdoc_get_markets' => [
        'classname'     => 'mod_valuemapdoc\external\get_markets',
        'methodname'    => 'execute',
        'classpath'     => '',
        'description'   => 'Get children records for hierarchy selector',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'mod/valuemapdoc:view',
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_valuemapdoc_create_entries_bulk' => [
        'classname'   => 'mod_valuemapdoc\external\create_entries_bulk',
        'methodname'  => 'execute',
        'description' => 'Kopiuje wiele wpisów z mapy wartości na podstawie ich ID.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'mod/valuemapdoc:edit',
    ],
    'mod_valuemapdoc_move_bulk' => [
        'classname'   => 'mod_valuemapdoc\external\move_entry_to_master_bulk',
        'methodname'  => 'execute',
        'description' => 'Przenosi wiele wpisów do wskazanej instancji valuemapdoc jako master.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'mod/valuemapdoc:manageentries',
    ],
    'mod_valuemapdoc_move_entry_to_master' => [
        'classname'   => 'mod_valuemapdoc\external\move_entry_to_master',
        'methodname'  => 'execute',
        'description' => 'Przenosi wpis do innej instancji valuemapdoc z ustawieniem jako master',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'mod/valuemapdoc:manageentries'
    ],
];