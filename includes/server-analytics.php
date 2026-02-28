<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function agent_monitor_track_visit() {
    if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) return;

    // @todo: Ensure collection enabled

    $req_path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : "";
    if ( ! $req_path || agent_monitor_is_system_request( $req_path ) ) return;

    $req_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : "";
    if ( ! $req_method ) return;

    $req_headers    = agent_monitor_get_request_headers();
    $req_query      = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : "";
    $req_time       = isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ? (int) ( floatval( $_SERVER['REQUEST_TIME_FLOAT'] ) * 1000 ) : time() * 1000;
    $req_http_ver_parts = isset( $_SERVER['SERVER_PROTOCOL'] ) ? explode( '/', sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) ) : [];
    $req_http_ver       = $req_http_ver_parts[1] ?? "";

    $res_status_code = http_response_code();

    $payload = array(
        'req_query'         => $req_query,
        'req_path'          => $req_path,
        'req_method'        => $req_method,
        'req_time'          => $req_time,
        'req_headers'       => $req_headers,
        'req_http_version'  => $req_http_ver,
        'res_status_code'   => $res_status_code,
        'source'            => 'wordpress/' . AGENT_MONITOR_PLUGIN_VERSION,
    );

    agent_monitor_append_to_log( $payload );
    agent_monitor_upload_log_if_needed();
}

add_action( 'shutdown', 'agent_monitor_track_visit' );

function agent_monitor_append_to_log( array $visit ) {
    $file_size     = file_exists( AGENT_MONITOR_VISITS_LOG_PATH ) ? filesize( AGENT_MONITOR_VISITS_LOG_PATH ) : 0;
    $log_max_bytes = (int) get_option( AGENT_MONITOR_LOG_MAX_SIZE, 32 ) * 1048576;

    if ( $file_size >= $log_max_bytes ) {
        return;
    }

    $file_handle = fopen( AGENT_MONITOR_VISITS_LOG_PATH, 'a' );

    if ( $file_handle === false ) {
        return;
    }

    if ( ! flock( $file_handle, LOCK_EX | LOCK_NB ) ) {
        fclose( $file_handle );
        return;
    }

    fwrite( $file_handle, wp_json_encode( $visit ) . PHP_EOL );
    flock( $file_handle, LOCK_UN );
    fclose( $file_handle );
}

function agent_monitor_upload_log_if_needed() {
    $last_upload = get_option( AGENT_MONITOR_LAST_LOG_UPLOAD, 0 );

    if ( ( time() - $last_upload ) > AGENT_MONITOR_LOG_UPLOAD_INTERVAL ) {
        update_option( AGENT_MONITOR_LAST_LOG_UPLOAD, time(), false );
        agent_monitor_upload_log();
    }
}

function agent_monitor_upload_log() {
    $file_handle = fopen( AGENT_MONITOR_VISITS_LOG_PATH, 'r+' );

    if ( $file_handle === false ) {
        return;
    }

    if ( ! flock( $file_handle, LOCK_EX | LOCK_NB ) ) {
        fclose( $file_handle );
        return;
    }

    $ndjson_content = stream_get_contents( $file_handle );

    ftruncate( $file_handle, 0 );
    flock( $file_handle, LOCK_UN );
    fclose( $file_handle );

    if ( empty( $ndjson_content ) ) {
        return;
    }

    $token = get_option( AGENT_MONITOR_TOKEN );

    if ( ! $token ) {
        return;
    }

    if ( function_exists( 'gzencode' ) ) {
        $body    = gzencode( $ndjson_content, 6 );
        $headers = array(
            'Authorization'    => 'Bearer ' . $token,
            'Content-Type'     => 'text/plain',
            'Content-Encoding' => 'gzip',
        );
    } else {
        $body    = $ndjson_content;
        $headers = array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'text/plain',
        );
    }

    wp_remote_post( 'https://ingest.eu.agentmonitor.io/logs/wordpress', array(
        'headers'  => $headers,
        'body'     => $body,
        'blocking' => false,
    ) );
}

function agent_monitor_is_system_request( $request_path ): bool {
    return (
        stripos( $request_path, '/wp-admin' ) === 0 ||
        stripos( $request_path, '/wp-login' ) === 0 ||
        stripos( $request_path, '/wp-cron' ) === 0 ||
        stripos( $request_path, '/wp-json' ) === 0 ||
        stripos( $request_path, '/wp-includes' ) === 0 ||
        stripos( $request_path, '/wp-content' ) === 0
    );
}

function agent_monitor_get_request_headers(): array {
    $allowed_headers = [
        'User-Agent',
        'Referer',
        'Host',
        'From',
        'Origin',
        'Accept',
        'Accept-Language',
        'Accept-Encoding',
        'Connection',
        'DNT',
        'X-Country-Code',
        'Signature',
        'Signature-Agent',
        'Signature-Input',
        'Content-Type',
        'Content-Length',
        'Content-Encoding',
        'Content-Language',
        'Sec-Fetch-Site',
        'Sec-Fetch-Mode',
        'Sec-Fetch-User',
        'Sec-Fetch-Dest',
        'Sec-CH-UA',
        'Sec-CH-UA-Mobile',
        'Sec-CH-UA-Platform',
        'Sec-CH-UA-Platform-Version',
        'Sec-CH-UA-Arch',
        'Sec-CH-UA-Bitness',
        'Sec-CH-UA-Model',
        'Sec-CH-UA-Full-Version',
        'Sec-CH-UA-Full-Version-List',
        'Remote-Addr',
        'X-Forwarded-For',
        'X-Real-IP',
        'Client-IP',
        'CF-Connecting-IP',
        'X-Cluster-Client-IP',
        'Forwarded',
        'X-Original-Forwarded-For',
        'Fastly-Client-IP',
        'True-Client-IP',
        'X-Appengine-User-IP',
    ];

    $request_headers = [];

    foreach ( $allowed_headers as $header_name ) {
        $header_value = agent_monitor_get_request_header_value( $header_name );

        if ( $header_value ) {
            $request_headers[ $header_name ] = $header_value;
        }
    }

    return $request_headers;
}

function agent_monitor_get_request_header_value( $header_name ) {
    $server_key                  = strtoupper( str_replace( '-', '_', $header_name ) );
    $server_key_with_http_prefix = 'HTTP_' . $server_key;

    if ( isset( $_SERVER[ $server_key ] ) ) {
        return sanitize_text_field( wp_unslash( $_SERVER[ $server_key ] ) );
    } elseif ( isset( $_SERVER[ $server_key_with_http_prefix ] ) ) {
        return sanitize_text_field( wp_unslash( $_SERVER[ $server_key_with_http_prefix ] ) );
    } elseif ( function_exists( 'getallheaders' ) ) {
        $headers_with_lowercase_keys = array_change_key_case( getallheaders(), CASE_LOWER );
        $lowercased_header_name      = strtolower( $header_name );

        if (isset($headers_with_lowercase_keys[$lowercased_header_name])) {
            return sanitize_text_field($headers_with_lowercase_keys[$lowercased_header_name]);
        } else {
            return null;
        }
    } else {
        return null;
    }
}
