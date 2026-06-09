<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function agent_monitor_is_enabled(): bool {
    return (bool) get_option( AGENT_MONITOR_IS_ENABLED, true )
        && (bool) get_option( AGENT_MONITOR_TOKEN );
}
