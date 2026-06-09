<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function agent_monitor_last_flush_time() {
    return file_exists( AGENT_MONITOR_EVENT_LOG_FLUSH_PATH )
        ? filemtime( AGENT_MONITOR_EVENT_LOG_FLUSH_PATH )
        : false;
}

function agent_monitor_is_enabled(): bool {
    return (bool) get_option( AGENT_MONITOR_IS_ENABLED, true )
        && (bool) get_option( AGENT_MONITOR_TOKEN );
}
