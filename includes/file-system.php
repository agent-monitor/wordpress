<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function agent_monitor_create_files_dir() {
    if ( ! file_exists( AGENT_MONITOR_FILES_DIR ) ) {
        wp_mkdir_p( AGENT_MONITOR_FILES_DIR );
    }

    $htaccess_file = AGENT_MONITOR_FILES_DIR . '/.htaccess';
    if ( ! file_exists( $htaccess_file ) ) {
        file_put_contents( $htaccess_file, 'deny from all' );
    }
}
