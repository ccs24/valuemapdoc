<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_valuemapdoc';   // Full name of the plugin (used for diagnostics)
$plugin->version   = 2025080106;          // The current plugin version (YYYYMMDDXX)
$plugin->requires  = 2022041900;          // Requires this Moodle version
$plugin->cron      = 0;                   // Cron interval in seconds (0 = no cron)
$plugin->maturity  = MATURITY_STABLE;     // Development stage: MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC, MATURITY_STABLE
$plugin->release   = '1.0.5';            // Human readable version name
