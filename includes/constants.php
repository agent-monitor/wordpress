<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// General

define( 'AGENT_MONITOR_PLUGIN_VERSION', '1.0.1' );
define( 'AGENT_MONITOR_FILES_DIR', wp_upload_dir()['basedir'] . '/agent-monitor' );

// Option groups

define( 'AGENT_MONITOR_OPTION_GROUP', 'agent_monitor_option_group' );

// Option names - user-controlled

define( 'AGENT_MONITOR_IS_ENABLED', 'agent_monitor_is_enabled' );
define( 'AGENT_MONITOR_TOKEN', 'agent_monitor_token' );
define( 'AGENT_MONITOR_LOG_MAX_SIZE', 'agent_monitor_log_max_size' );

// Event collection

define( 'AGENT_MONITOR_EVENT_LOG_PATH', AGENT_MONITOR_FILES_DIR . '/visits.log' );
define( 'AGENT_MONITOR_EVENT_LOG_FLUSH_PATH', AGENT_MONITOR_FILES_DIR . '/visits.flush' );
define( 'AGENT_MONITOR_EVENT_LOG_FLUSH_INTERVAL', 30 ); // seconds between uploads
define( 'AGENT_MONITOR_EVENT_LOG_SIZE_MIN', 8 * MB_IN_BYTES);
define( 'AGENT_MONITOR_EVENT_LOG_SIZE_MAX', 64 * MB_IN_BYTES);