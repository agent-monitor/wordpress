<?php

function agent_monitor_track_visit() {
    // Exit early on admin, AJAX or cron requests.
    if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) return;

    $token = get_option(AGENT_MONITOR_TOKEN);

    // Don't proceed if token not set.
    if (!$token) return;

    // Sanitize the request URI
    $request_path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) ) : false;
    $request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( $_SERVER['REQUEST_METHOD'] ) : false;

    // Don't track internal WordPress system requests.
    if ( ! $request_path || agent_monitor_is_system_request( $request_path ) ) return;

    // Sanitize all other request components.
    $request_headers = agent_monitor_get_request_headers();
    $request_query = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : false;
    $request_time = isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ? (int) ( $_SERVER['REQUEST_TIME_FLOAT'] * 1000 ) : (int) ( current_time( 'timestamp', true ) * 1000 );

    $http_version_parts = isset( $_SERVER['SERVER_PROTOCOL'] ) ? explode( '/', sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) ) : [];
    $request_http_version = $http_version_parts[1] ?? false;

    // A valid method is required.
    if (!$request_method) return;

    $headers = array(
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json',
    );

    $body = array(
        'path' => $request_path,
        'method' => $request_method,
        'headers' => $request_headers,
        'query' => $request_query,
        'http_version' => $request_http_version,
        'request_time' => $request_time,
        'source' => 'wordpress/' . AGENT_MONITOR_PLUGIN_VERSION,
    );

    // Send the data using a non-blocking request for performance.
    wp_remote_post('https://api.agentmonitor.io/v1/track', array(
        'headers' => $headers,
        'body' => wp_json_encode($body),
        'blocking' => false,
    ));
}

add_action('wp_loaded', 'agent_monitor_track_visit');

function agent_monitor_is_system_request($request_path): bool {
    return (
        stripos($request_path, '/wp-admin') === 0 ||
        stripos($request_path, '/wp-login') === 0 ||
        stripos($request_path, '/wp-cron') === 0 ||
        stripos($request_path, '/wp-json') === 0 ||
        stripos($request_path, '/wp-includes') === 0 ||
        stripos($request_path, '/wp-content') === 0
    );
}

function agent_monitor_get_request_headers(): array {
    $whitelist = [
        'Signature',
        'Signature-Agent',
        'Signature-Input',
        'Origin',
        'Referer',
        'From',
        'Accept-Language',
        'User-Agent',
        'X-Country-Code',
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

    foreach ($whitelist as $header_name) {
        $header_value = agent_monitor_get_request_header_value($header_name);

        if ($header_value) {
            $request_headers[$header_name] = $header_value;
        }
    }

    return $request_headers;
}

function agent_monitor_get_request_header_value($header_name) {
    $server_key = strtoupper(str_replace('-', '_', $header_name));
    $server_key_with_http_prefix = 'HTTP_' . $server_key;

    if (isset($_SERVER[$server_key])) {
        return sanitize_text_field(wp_unslash($_SERVER[$server_key]));
    } else if (isset($_SERVER[$server_key_with_http_prefix])) {
        return sanitize_text_field(wp_unslash($_SERVER[$server_key_with_http_prefix]));
    } else if (function_exists('getallheaders')) {
        $headers_with_lowercase_keys = array_change_key_case(getallheaders(), CASE_LOWER);
        $lowercased_header_name = strtolower($header_name);

        return $headers_with_lowercase_keys[$lowercased_header_name] ?? null;
    } else {
        return null;
    }
}
