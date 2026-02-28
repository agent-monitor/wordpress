<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AGENT_MONITOR_SETTINGS_GROUP', 'agent_monitor_settings_group' );

define( 'AGENT_MONITOR_TOKEN', 'agent_monitor_token' );
define( 'AGENT_MONITOR_LAST_LOG_UPLOAD', 'agent_monitor_last_log_upload' );
define( 'AGENT_MONITOR_LOG_MAX_SIZE', 'agent_monitor_log_max_size' );

define( 'AGENT_MONITOR_FILES_DIR', wp_upload_dir()['basedir'] . '/agent-monitor' );
define( 'AGENT_MONITOR_VISITS_LOG_PATH', AGENT_MONITOR_FILES_DIR . '/visits.log' );
define( 'AGENT_MONITOR_LOG_UPLOAD_INTERVAL', 30 ); // seconds between uploads