<?php
// This file keeps track of upgrades to the valuemapdoc module
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for the valuemapdoc module.
 *
 * @param int $oldversion The old version of the module.
 * @return bool True on success, false on failure.
 */
function xmldb_valuemapdoc_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025041720) {

        // Add parentid field to valuemapdoc_promptlog table.
        $table = new xmldb_table('valuemapdoc_promptlog');
        $field = new xmldb_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'response');

        // Conditionally launch add field parentid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Valuemapdoc savepoint reached.
        upgrade_mod_savepoint(true, 2025041720, 'valuemapdoc');
    }

    if ($oldversion < 2025041728) {
        $tables = ['valuemapdoc_entries', 'valuemapdoc_templates', 'valuemapdoc_promptlog', 'valuemapdoc_content', 'valuemapdoc_documents'];
        foreach ($tables as $tablename) {
            $table = new xmldb_table($tablename);
            $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        // Valuemapdoc savepoint reached.
        upgrade_mod_savepoint(true, 2025041728, 'valuemapdoc');
       
    }

    if ($oldversion < 2025041730) {
        $table = new xmldb_table('valuemapdoc_content');
        $field = new xmldb_field('visibility', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'groupid');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Valuemapdoc savepoint reached.
        upgrade_mod_savepoint(true, 2025041730, 'valuemapdoc');
    }

    if ($oldversion < 2025041740) {
        // Add 'status' field to valuemapdoc_content table.
        $table = new xmldb_table('valuemapdoc_content');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '20', null, null, null, 'pending', 'groupid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add 'description' field to valuemapdoc_templates table.
        $table = new xmldb_table('valuemapdoc_templates');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'groupid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add 'fields' field to valuemapdoc_templates table.
        $field = new xmldb_field('fields', XMLDB_TYPE_TEXT, null, null, null, null, null, 'description');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Valuemapdoc savepoint reached.
        upgrade_mod_savepoint(true, 2025041740, 'valuemapdoc');
    }

    
    if ($oldversion < 2025052300) {
        
        // Define table valuemapdoc_templates to be modified
        $table = new xmldb_table('valuemapdoc_templates');
        
        // Define field courseid to be added to valuemapdoc_templates
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'groupid');
        
        // Conditionally launch add field courseid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field userid to be added to valuemapdoc_templates
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'courseid');
        
        // Conditionally launch add field userid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field scope to be added to valuemapdoc_templates
        $field = new xmldb_field('scope', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'system', 'userid');
        
        // Conditionally launch add field scope
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field createdby to be added to valuemapdoc_templates
        $field = new xmldb_field('createdby', XMLDB_TYPE_INTEGER, '10', null, null, null, '1', 'scope');
    
        // Conditionally launch add field createdby
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field isactive to be added to valuemapdoc_templates
        $field = new xmldb_field('isactive', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'createdby');
        
        // Conditionally launch add field isactive
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update existing templates to have proper default values
        // Set createdby to admin user (ID = 2) or first available admin
        $adminuser = $DB->get_record('user', ['id' => 2]);
        if (!$adminuser) {
            // If admin user doesn't exist, get first user with admin capabilities
            $admins = get_admins();
            $adminid = !empty($admins) ? reset($admins)->id : 1;
        } else {
            $adminid = 2;
        }

        // Update all existing templates
        $DB->execute("UPDATE {valuemapdoc_templates} 
                     SET scope = 'system', 
                         courseid = 0, 
                         userid = 0, 
                         createdby = ?, 
                         isactive = 1 
                     WHERE createdby IS NULL OR createdby = 0", 
                     [$adminid]);

        // valuemapdoc savepoint reached
        upgrade_mod_savepoint(true, 2025052300, 'valuemapdoc');
    }

    if ($oldversion < 2025052301) {
        
        // Drop the old unused table
        $table = new xmldb_table('valuemapdoc_documents');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Create new markets table
        $table = new xmldb_table('valuemapdoc_markets');
        
        // Add fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('isactive', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Add keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        
        // Add indexes
        $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
        $table->add_index('type', XMLDB_INDEX_NOTUNIQUE, ['type']);
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $table->add_index('parentid', XMLDB_INDEX_NOTUNIQUE, ['parentid']);

        $dbman->create_table($table);

        upgrade_mod_savepoint(true, 2025052301, 'valuemapdoc');
    }

if ($oldversion < 2025052306) {  // Zwiększ do 2025052306
    
    error_log('VALUEMAPDOC UPGRADE: Starting upgrade to 2025052306 from version ' . $oldversion);
    
    $table = new xmldb_table('valuemapdoc_markets');
    
    // Sprawdź każde pole osobno
    $baseinfo = new xmldb_field('baseinfo', XMLDB_TYPE_TEXT, null, null, null, null, null, 'description');
    if (!$dbman->field_exists($table, $baseinfo)) {
        error_log('VALUEMAPDOC UPGRADE: Adding baseinfo field');
        $dbman->add_field($table, $baseinfo);
    } else {
        error_log('VALUEMAPDOC UPGRADE: baseinfo field already exists');
    }
    
    $outsideinfo = new xmldb_field('outsideinfo', XMLDB_TYPE_TEXT, null, null, null, null, null, 'baseinfo');
    if (!$dbman->field_exists($table, $outsideinfo)) {
        error_log('VALUEMAPDOC UPGRADE: Adding outsideinfo field');
        $dbman->add_field($table, $outsideinfo);
    } else {
        error_log('VALUEMAPDOC UPGRADE: outsideinfo field already exists');
    }
    
    $insideinfo = new xmldb_field('insideinfo', XMLDB_TYPE_TEXT, null, null, null, null, null, 'outsideinfo');
    if (!$dbman->field_exists($table, $insideinfo)) {
        error_log('VALUEMAPDOC UPGRADE: Adding insideinfo field');
        $dbman->add_field($table, $insideinfo);
    } else {
        error_log('VALUEMAPDOC UPGRADE: insideinfo field already exists');
    }
    
    error_log('VALUEMAPDOC UPGRADE: Completed upgrade to 2025052306');
    upgrade_mod_savepoint(true, 2025052306, 'valuemapdoc');
}

if ($oldversion < 2025052307) {

        // Definicja tabeli valuemapdoc_markets
        $table = new xmldb_table('valuemapdoc_markets');

        // Definicja nowego pola custom_fields
        $field = new xmldb_field('custom_fields', XMLDB_TYPE_TEXT, null, null, null, null, null, 'insideinfo');

        // Dodaj pole jeśli nie istnieje
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Zapisz punkt kontrolny upgrade'u
        upgrade_mod_savepoint(true, 2025052307, 'valuemapdoc');
    }

 if ($oldversion < 2025052309) {
        // Add dynamic_fields JSON column
        $table = new xmldb_table('valuemapdoc_markets');
        
        $field = new xmldb_field('dynamic_fields', XMLDB_TYPE_TEXT, null, null, null, null, null, 'insideinfo');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2025052309, 'valuemapdoc');
    }

if ($oldversion < 2025052310) {
        // Optional: Remove old columns after successful migration
        // Uncomment these if you want to remove the old columns
        
        $table = new xmldb_table('valuemapdoc_markets');
        
        $field = new xmldb_field('baseinfo');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        $field = new xmldb_field('outsideinfo');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        $field = new xmldb_field('insideinfo');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        
        upgrade_mod_savepoint(true, 2025052310, 'valuemapdoc');
    }

if ($oldversion < 2025052311) {

        // Define table valuemapdoc_markets to be modified
        $table = new xmldb_table('valuemapdoc_markets');

        // Step 1: Add new cmid field
        $field = new xmldb_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');

        // Conditionally add field cmid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Step 3: Drop the custom_fields field if it exists
        $custom_fields_field = new xmldb_field('custom_fields');
        if ($dbman->field_exists($table, $custom_fields_field)) {
            $dbman->drop_field($table, $custom_fields_field);
        }
        // Step 4: Add index for cmid
        $index = new xmldb_index('idx_cmid', XMLDB_INDEX_NOTUNIQUE, ['cmid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Step 5: Add index for cmid + type combination for better performance
        $index_cmid_type = new xmldb_index('idx_cmid_type', XMLDB_INDEX_NOTUNIQUE, ['cmid', 'type']);
        if (!$dbman->index_exists($table, $index_cmid_type)) {
            $dbman->add_index($table, $index_cmid_type);
        }

        // Alternatively, keep courseid for reference but make it nullable
        /*$courseid_field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'cmid');
        if ($dbman->field_exists($table, $courseid_field)) {
            $dbman->change_field_notnull($table, $courseid_field);
        }*/

        // Valuemapdoc savepoint reached.
        upgrade_mod_savepoint(true, 2025052311, 'valuemapdoc');

    }

    if ($oldversion < 2025052313) {
        $tables = ['valuemapdoc_content', 'valuemapdoc_promptlog'];
        $fields = ['marketid', 'customerid', 'personid', 'opportunityid'];

        foreach ($tables as $tablename) {
            $table = new xmldb_table($tablename);

            // Usuń stare pola
            if ($tablename === 'valuemapdoc_content') {
                $oldfield = new xmldb_field('opportunityname');
                if ($dbman->field_exists($table, $oldfield)) {
                    $dbman->drop_field($table, $oldfield);
                }
            }
            if ($tablename === 'valuemapdoc_promptlog') {
                $oldfield = new xmldb_field('opportunity');
                if ($dbman->field_exists($table, $oldfield)) {
                    $dbman->drop_field($table, $oldfield);
                }
            }

            // Dodaj nowe pola
            foreach ($fields as $fieldname) {
                $field = new xmldb_field($fieldname, XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
                if (!$dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }
            }
        }

        upgrade_mod_savepoint(true, 2025052313, 'valuemapdoc');
    }

    // Fix for missing 'course' field in main table - critical for Moodle calendar events
    if ($oldversion < 2025080101) {
        
        // Add missing 'course' field to main table
        $table = new xmldb_table('valuemapdoc');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            
            // Try to populate course field based on course_modules table
            $sql = "UPDATE {valuemapdoc} v 
                    SET course = (
                        SELECT cm.course 
                        FROM {course_modules} cm 
                        WHERE cm.instance = v.id 
                        AND cm.module = (SELECT id FROM {modules} WHERE name = 'valuemapdoc')
                        LIMIT 1
                    )
                    WHERE v.course = 0";
            
            try {
                $DB->execute($sql);
            } catch (Exception $e) {
                // If we can't auto-populate, log warning but continue
                debugging('Could not auto-populate course field in valuemapdoc table: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }

        // Add foreign key and index for course field
        $key = new xmldb_key('fk_course', XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));
        if (!$dbman->find_key_name($table, $key)) {
            $dbman->add_key($table, $key);
        }

        $index = new xmldb_index('idx_course', XMLDB_INDEX_NOTUNIQUE, array('course'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_mod_savepoint(true, 2025080101, 'valuemapdoc');
    }

    // Fix duplicate 'name' field issue and field formatting
    if ($oldversion < 2025080102) {
        
        $table = new xmldb_table('valuemapdoc');
        
        // Check if we have a formatting issue with activity_prompt field
        $field = new xmldb_field('activity_prompt', XMLDB_TYPE_TEXT, null, null, null, null, null, 'ismaster');
        if ($dbman->field_exists($table, $field)) {
            // Recreate field with proper definition
            $dbman->drop_field($table, $field);
            $field = new xmldb_field('activity_prompt', XMLDB_TYPE_TEXT, null, null, null, null, null, 'ismaster');
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2025080102, 'valuemapdoc');
    }

    // Fix valuemapdoc_markets table - remove invalid foreign key
    if ($oldversion < 2025080103) {
        
        $table = new xmldb_table('valuemapdoc_markets');
        
        if ($dbman->table_exists($table)) {
            // Check if courseid field exists, if not the foreign key is invalid anyway
            $courseid_field = new xmldb_field('courseid');
            if (!$dbman->field_exists($table, $courseid_field)) {
                // Remove the invalid foreign key reference from any existing definition
                // This is handled automatically by Moodle when the field doesn't exist
                debugging('Invalid foreign key reference to non-existent courseid field was found in valuemapdoc_markets', DEBUG_DEVELOPER);
            }
            
            // Ensure proper indexes exist
            $index_cmid = new xmldb_index('idx_cmid', XMLDB_INDEX_NOTUNIQUE, array('cmid'));
            if (!$dbman->index_exists($table, $index_cmid)) {
                $dbman->add_index($table, $index_cmid);
            }

            $index_type = new xmldb_index('idx_type', XMLDB_INDEX_NOTUNIQUE, array('type'));
            if (!$dbman->index_exists($table, $index_type)) {
                $dbman->add_index($table, $index_type);
            }

            $index_user = new xmldb_index('idx_user', XMLDB_INDEX_NOTUNIQUE, array('userid'));
            if (!$dbman->index_exists($table, $index_user)) {
                $dbman->add_index($table, $index_user);
            }
        }

        upgrade_mod_savepoint(true, 2025080103, 'valuemapdoc');
    }

    // Add calendar support functions
    if ($oldversion < 2025080104) {
        
        // This upgrade step ensures that lib.php contains the necessary calendar functions
        // The actual functions need to be added to lib.php manually:
        // - valuemapdoc_refresh_events()
        // - valuemapdoc_core_calendar_provide_event_action()
        
        upgrade_mod_savepoint(true, 2025080104, 'valuemapdoc');
    }

    // Validate data integrity after all upgrades
    if ($oldversion < 2025080105) {
        
        // Check for any records with course = 0 and try to fix them
        $invalid_records = $DB->count_records('valuemapdoc', array('course' => 0));
        if ($invalid_records > 0) {
            debugging("Found {$invalid_records} valuemapdoc records with course = 0. Please check your data.", DEBUG_DEVELOPER);
            
            // Try one more time to fix based on course_modules
            $sql = "UPDATE {valuemapdoc} v 
                    SET course = (
                        SELECT cm.course 
                        FROM {course_modules} cm 
                        WHERE cm.instance = v.id 
                        AND cm.module = (SELECT id FROM {modules} WHERE name = 'valuemapdoc')
                        LIMIT 1
                    )
                    WHERE v.course = 0 
                    AND EXISTS (
                        SELECT 1 FROM {course_modules} cm 
                        WHERE cm.instance = v.id 
                        AND cm.module = (SELECT id FROM {modules} WHERE name = 'valuemapdoc')
                    )";
            
            try {
                $DB->execute($sql);
                $remaining = $DB->count_records('valuemapdoc', array('course' => 0));
                if ($remaining > 0) {
                    debugging("Still have {$remaining} records with course = 0 after upgrade. Manual intervention may be required.", DEBUG_DEVELOPER);
                }
            } catch (Exception $e) {
                debugging('Error fixing course field: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }

        upgrade_mod_savepoint(true, 2025080105, 'valuemapdoc');
    }


        // Add 'name' field to valuemapdoc_content table
    if ($oldversion < 2025080106) {
        $table = new xmldb_table('valuemapdoc_content');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'status');

        if (!$dbman->field_exists($table, $field)) { 
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2025080106, 'valuemapdoc');
    }









    return true;
}