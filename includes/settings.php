<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register settings

function agent_monitor_register_settings() {
    register_setting( AGENT_MONITOR_OPTION_GROUP, AGENT_MONITOR_IS_ENABLED, array(
        'type'              => 'boolean',
        'label'             => __( 'Enable Analytics', 'agentmonitor' ),
        'description'       => __( 'Whether visit tracking is enabled.', 'agentmonitor' ),
        'sanitize_callback' => 'rest_sanitize_boolean',
        'show_in_rest'      => false,
        'default'           => true,
    ) );

    register_setting( AGENT_MONITOR_OPTION_GROUP, AGENT_MONITOR_TOKEN, array(
        'type'              => 'string',
        'label'             => __( 'Site Token', 'agentmonitor' ),
        'description'       => __( 'The access token linking this site to your Agent Monitor site.', 'agentmonitor' ),
        'sanitize_callback' => 'sanitize_text_field',
        'show_in_rest'      => false,
        'default'           => '',
    ) );

    register_setting( AGENT_MONITOR_OPTION_GROUP, AGENT_MONITOR_LOG_MAX_SIZE, array(
        'type'              => 'integer',
        'label'             => __( 'Maximum Log Size', 'agentmonitor' ),
        'description'       => __( 'Maximum size of the visit log file in megabytes before new visits are dropped.', 'agentmonitor' ),
        'sanitize_callback' => 'agent_monitor_sanitize_log_max_size',
        'show_in_rest'      => false,
        'default'           => 16,
    ) );
}

function agent_monitor_sanitize_log_max_size( $value ) {
    $min   = (int) ( AGENT_MONITOR_EVENT_LOG_SIZE_MIN / MB_IN_BYTES );
    $max   = (int) ( AGENT_MONITOR_EVENT_LOG_SIZE_MAX / MB_IN_BYTES );
    $value = max( $min, min( $max, (int) $value ) );
    return (int) ( round( $value / 8 ) * 8 );
}

add_action('admin_init', 'agent_monitor_register_settings');

// Register menu item

function agent_monitor_menu() {
    add_menu_page(
        'Agent Monitor',
        'Agent Monitor',
        'manage_options',
        'agentmonitor',
        'agent_monitor_page'
    );
}

add_action('admin_menu', 'agent_monitor_menu');

function agent_monitor_enqueue_settings_styles( $hook ) {
    if ( $hook !== 'toplevel_page_agentmonitor' ) {
        return;
    }

    wp_register_style(
        'agentmonitor-settings',
        plugin_dir_url( __FILE__ ) . '../assets/css/main.css',
        [],
        AGENT_MONITOR_PLUGIN_VERSION
    );
    wp_enqueue_style( 'agentmonitor-settings' );

}

add_action( 'admin_enqueue_scripts', 'agent_monitor_enqueue_settings_styles' );

function agent_monitor_page() {
    // Prevent unauthorized access
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Detect updates
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error( 'agent_monitor_messages', 'agent_monitor_message', __( 'Settings Saved', 'agentmonitor' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'agent_monitor_messages' );

    ?>
    <div id="agentmonitor-page">
        <div class="header">
            <div class="head">
                <svg class="logo" viewBox="0 0 1167 177" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M176.281 38.8892C188.762 38.8893 201.244 44.6366 207.389 53.2571V40.4221H226.014V141.572C226.014 162.261 211.805 176.055 190.107 176.055H143.445V160.537H188.186C199.516 160.537 207.388 152.299 207.389 140.422V124.713C202.588 132.951 190.106 138.506 176.089 138.506C147.286 138.506 130.58 117.242 130.58 88.6978C130.58 60.1539 147.094 38.8892 176.281 38.8892ZM179.162 55.1728C161.112 55.1728 149.398 68.5831 149.398 88.6978C149.398 108.812 161.112 122.223 179.162 122.223C197.019 122.223 208.732 108.812 208.733 88.6978C208.732 68.5832 197.019 55.1729 179.162 55.1728Z" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M285.815 38.8892C315.578 38.8895 331.515 60.1545 331.515 84.6755C331.515 87.1657 331.324 90.4223 331.132 92.1463H257.396C258.932 110.154 270.837 122.607 287.543 122.607C301.752 122.607 310.97 116.476 313.658 105.556H332.284C329.02 125.479 311.545 138.506 287.543 138.506C257.588 138.506 239.154 114.944 239.153 88.1241C239.153 61.1125 256.052 38.8892 285.815 38.8892ZM285.815 53.832C270.837 53.832 260.852 62.0698 257.972 77.7785H312.698C311.546 63.0278 301.752 53.8322 285.815 53.832Z" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M731.262 38.8892C760.257 38.8892 780.995 59.7707 780.995 88.6978C780.995 117.625 760.257 138.506 731.262 138.506C702.267 138.506 681.336 117.625 681.336 88.6978C681.337 59.7707 702.267 38.8892 731.262 38.8892ZM731.262 55.1728C712.828 55.1728 700.346 68.9661 700.346 88.6978C700.346 108.429 712.828 122.223 731.262 122.223C749.504 122.223 761.985 108.429 761.986 88.6978C761.985 68.9661 749.504 55.1728 731.262 55.1728Z" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M1049.01 38.8892C1078.01 38.8892 1098.74 59.7707 1098.74 88.6978C1098.74 117.625 1078.01 138.506 1049.01 138.506C1020.02 138.506 999.085 117.625 999.085 88.6978C999.085 59.7707 1020.02 38.8892 1049.01 38.8892ZM1049.01 55.1728C1030.58 55.1728 1018.09 68.9661 1018.09 88.6978C1018.09 108.429 1030.58 122.223 1049.01 122.223C1067.25 122.223 1079.73 108.429 1079.73 88.6978C1079.73 68.9661 1067.25 55.1728 1049.01 55.1728Z" />
                    <path d="M630.953 14.6859C637.715 -6.60444 669.209 -1.74591 669.21 20.5877V137.098H650.52V20.5877C650.52 20.2383 650.462 20.0778 650.449 20.0439C650.435 20.0083 650.424 19.9951 650.41 19.9803C650.363 19.9298 650.157 19.7657 649.761 19.7047C649.366 19.6437 649.12 19.7383 649.059 19.772C649.041 19.7819 649.027 19.7913 649.003 19.8206C648.979 19.8497 648.876 19.9862 648.77 20.3183L615.666 124.549C609.891 142.732 584.099 142.732 578.323 124.549L545.22 20.3195C545.114 19.9857 545.01 19.85 544.987 19.8219C544.963 19.7925 544.95 19.7831 544.932 19.7733C544.872 19.7397 544.625 19.6448 544.228 19.7059C543.833 19.7671 543.627 19.9313 543.579 19.9815C543.566 19.9962 543.556 20.0101 543.542 20.0451C543.528 20.0798 543.469 20.2407 543.469 20.5889V137.098H524.78V20.5889C524.781 -1.74466 556.274 -6.60321 563.037 14.6871L596.14 118.916C596.212 119.14 596.284 119.25 596.315 119.293C596.349 119.338 596.381 119.368 596.419 119.394C596.507 119.454 596.705 119.54 596.994 119.54C597.283 119.54 597.481 119.454 597.569 119.394C597.607 119.368 597.64 119.338 597.673 119.293C597.705 119.251 597.777 119.141 597.848 118.916L630.953 14.6859Z" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M45.6452 13.1618C51.9313 -3.90646 76.127 -3.90633 82.4131 13.1618L128.06 137.099H108.147L94.872 101.055H33.1863L19.9123 137.099H0L45.6452 13.1618ZM64.0298 19.0062C63.7648 19.0062 63.5755 19.084 63.481 19.1459C63.4392 19.1734 63.4023 19.2053 63.366 19.2507C63.3309 19.2948 63.2607 19.3961 63.1884 19.5924L39.1119 84.9661H88.9464L64.8699 19.5924C64.7978 19.3967 64.7275 19.2962 64.6924 19.2519C64.656 19.2063 64.6192 19.1735 64.5774 19.1459C64.4831 19.084 64.2945 19.0064 64.0298 19.0062Z" />
                    <path d="M395.185 38.8892C417.075 38.8895 431.284 54.5993 431.284 77.5876V136.975H412.658V81.227C412.658 65.9013 403.633 55.5569 389.999 55.5569C374.83 55.5572 364.077 67.8174 364.077 84.6755V136.975H345.451V40.4221H364.077V57.4726C369.261 45.4037 380.783 38.8892 395.185 38.8892Z" />
                    <path d="M474.915 40.4221H503.527V56.5148H474.915V104.791C474.916 115.135 480.677 120.882 489.893 120.882H503.527V136.975H488.549C468.964 136.975 456.29 125.097 456.289 105.365V56.5148H436.511V40.4221H456.289V10.1535H474.915V40.4221Z" />
                    <path d="M843.744 38.8892C865.634 38.8893 879.844 54.5981 879.844 77.5864V136.974H861.217V81.2257C861.216 65.9007 852.192 55.5559 838.559 55.5557C823.39 55.5557 812.637 67.8163 812.636 84.6743V136.974H794.009V40.4221H812.636V57.4714C817.821 45.4027 829.342 38.8892 843.744 38.8892Z" />
                    <path d="M914.158 136.974H895.531V40.4221H914.158V136.974Z" />
                    <path d="M966.889 40.4221H995.501V56.5135H966.889V104.789C966.889 115.134 972.65 120.882 981.867 120.882H995.501V136.974H980.523C960.937 136.974 948.263 125.096 948.263 105.364V56.5135H928.484V40.4221H948.263V10.1535H966.889V40.4221Z" />
                    <path d="M1129.1 59.0042C1133.52 46.1692 1141.2 40.4221 1154.84 40.4221H1166.55V56.5135H1152.53C1134.87 56.5135 1129.1 72.2225 1129.1 93.295V136.974H1110.48V40.4221H1129.1V59.0042Z" />
                    <path d="M915.118 23.3715H894.379V0H915.118V23.3715Z" />
                </svg>
                <a class="button-secondary" href="https://app.agentmonitor.io" target="_blank">Open Dashboard ↗</a>
            </div>
            <div class="muted">
                Track AI Agents, crawlers, and scrapers browsing your website in realtime. Measure how often platforms like ChatGPT, Claude, Gemini, and Perplexity mention your website.
            </div>
            <div class="muted">
                Don't have an account yet? <a href="https://agentmonitor.io" target="_blank">Get started for free!</a>
            </div>
        </div>
        <form method="post" action="options.php">
            <?php settings_fields(AGENT_MONITOR_OPTION_GROUP); ?>
            <div class="content">
                <div class="section">
                    <h2>Connect Your Site</h2>
                    <p>Copy and paste your access token from your site's <a href="https://app.agentmonitor.io/integrate" target="_blank">Integrate</a> page.</p>
                    <input
                        type="password"
                        placeholder="Paste your access token here"
                        id="<?php echo esc_attr(AGENT_MONITOR_TOKEN); ?>"
                        name="<?php echo esc_attr(AGENT_MONITOR_TOKEN); ?>"
                        value="<?php echo esc_attr(get_option(AGENT_MONITOR_TOKEN, '')); ?>"
                    />
                </div>
                <div class="section">
                    <h2>Setup Analytics</h2>
                    <label style="font-weight: bold;">
                        <input
                            type="checkbox"
                            id="<?php echo esc_attr( AGENT_MONITOR_IS_ENABLED ); ?>"
                            name="<?php echo esc_attr( AGENT_MONITOR_IS_ENABLED ); ?>"
                            value="1"
                            <?php checked( get_option( AGENT_MONITOR_IS_ENABLED, true ) ); ?>
                        />
                        <?php esc_html_e( 'Enable Analytics', 'agentmonitor' ); ?>
                    </label>
                    <p>Realtime activity can be seen on <a href="https://app.agentmonitor.io/" target="_blank">your dashboard</a> within a few seconds.</p>
                    <?php $flush_time = agent_monitor_last_flush_time(); ?>
                    <p class="muted" style="margin-top: 16px; font-size: 13px;">
                        <?php if ( $flush_time ): ?>
                            <?php
                            /* translators: %s: human-readable time difference, e.g. "2 minutes" */
                            printf(
                                esc_html__( 'Last event upload: %s ago', 'agentmonitor' ),
                                esc_html( human_time_diff( $flush_time ) )
                            );
                            ?>
                        <?php else: ?>
                            <?php esc_html_e( 'No event data has been uploaded yet.', 'agentmonitor' ); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="section">
                    <h2>Collection Settings</h2>
                    <label class="field-label" for="<?php echo esc_attr( AGENT_MONITOR_LOG_MAX_SIZE ); ?>">
                        <?php esc_html_e( 'Maximum log file size', 'agentmonitor' ); ?>: <output id="agentmonitor-log-max-size-output"><?php echo esc_html( get_option( AGENT_MONITOR_LOG_MAX_SIZE ) ); ?></output> MB
                    </label>
                    <input
                        type="range"
                        id="<?php echo esc_attr( AGENT_MONITOR_LOG_MAX_SIZE ); ?>"
                        name="<?php echo esc_attr( AGENT_MONITOR_LOG_MAX_SIZE ); ?>"
                        min="<?php echo esc_attr( (int) ( AGENT_MONITOR_EVENT_LOG_SIZE_MIN / MB_IN_BYTES ) ); ?>"
                        max="<?php echo esc_attr( (int) ( AGENT_MONITOR_EVENT_LOG_SIZE_MAX / MB_IN_BYTES ) ); ?>"
                        step="8"
                        value="<?php echo esc_attr( get_option( AGENT_MONITOR_LOG_MAX_SIZE ) ); ?>"
                        oninput="document.getElementById('agentmonitor-log-max-size-output').value = this.value"
                    />
                </div>
            </div>
            <div class="foot">
                <?php submit_button('', 'primary', 'submit', false); ?>
            </div>
        </form>
        <div class="muted">
            <strong>Important:</strong> If you are using plugins that generate cached versions of your pages (e.g., LiteSpeed Cache, WP Rocket), Agent Monitor will not be able to track these pages reliably. If that's the case, consider a different integration.
        </div>
    </div>
    <?php
}