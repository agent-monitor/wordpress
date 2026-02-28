<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function agent_monitor_register_settings() {
    register_setting( AGENT_MONITOR_SETTINGS_GROUP, AGENT_MONITOR_TOKEN, array(
        'type'              => 'string',
        'label'             => __( 'Site Token', 'agent-monitor' ),
        'description'       => __( 'The access token linking this site to your Agent Monitor site.', 'agent-monitor' ),
        'sanitize_callback' => 'sanitize_text_field',
        'show_in_rest'      => false,
        'default'           => '',
    ) );

    register_setting( AGENT_MONITOR_SETTINGS_GROUP, AGENT_MONITOR_LOG_MAX_SIZE, array(
        'type'              => 'integer',
        'label'             => __( 'Maximum Log Size', 'agent-monitor' ),
        'description'       => __( 'Maximum size of the visit log file in megabytes before new visits are dropped.', 'agent-monitor' ),
        'sanitize_callback' => 'agent_monitor_sanitize_log_max_size',
        'show_in_rest'      => false,
        'default'           => 32,
    ) );
}

function agent_monitor_sanitize_log_max_size( $value ) {
    $value = (int) $value;
    $value = max( 16, min( 128, $value ) );
    return (int) ( round( $value / 8 ) * 8 );
}

add_action('admin_init', 'agent_monitor_register_settings');

// Register menu item

function agent_monitor_menu() {
    add_menu_page(
        'Agent Monitor',
        'Agent Monitor',
        'manage_options',
        'agent-monitor',
        'agent_monitor_page'
    );
}

add_action('admin_menu', 'agent_monitor_menu');

function agent_monitor_page() {
    // Prevent unauthorized access
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Detect updates
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error( 'agent_monitor_messages', 'agent_monitor_message', __( 'Settings Saved', 'agent-monitor' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'agent_monitor_messages' );

    ?>
    <style>
        :root {}

        #agent-monitor-page {
            padding: 24px 24px 24px 4px;
            max-width: 512px;
            margin: 0 auto;
        }

        #agent-monitor-page .header {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        #agent-monitor-page h1,
        #agent-monitor-page h2 {
            margin: 0;
        }

        #agent-monitor-page p {
            margin: 16px 0;
        }

        #agent-monitor-page ol {
            margin: 16px 0 16px 24px;
        }

        #agent-monitor-page p:first-child {
            margin-top: 0;
        }

        #agent-monitor-page p:last-child {
            margin-bottom: 0;
        }

        #agent-monitor-page form {
            background: #ffffff;
            padding: 24px;
            border: 1px solid #b8bedf;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        #agent-monitor-page form .card-footer {
            display: flex;
            justify-content: end;
        }

        #agent-monitor-page form <?php echo esc_attr('#'.AGENT_MONITOR_TOKEN); ?> {
            width: 100%;
            line-height: 32px;
            padding: 0 8px;
            border: 1px solid #b8bedf;
        }

        #agent-monitor-page form input[type=submit] {
            padding: 0 16px;
            line-height: 32px;
        }

        #agent-monitor-page form .field-label {
            display: block;
            margin-bottom: 8px;
        }

        #agent-monitor-page form input[type=range] {
            width: 100%;
        }
    </style>
    <div class="wrap" id="agent-monitor-page">
        <div class="header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <a class="secondary-button" href="https://app.agentmonitor.io" target="_blank">Go to Agent Monitor ↗</a>
        </div>
        <form method="post" action="options.php">
            <?php settings_fields(AGENT_MONITOR_SETTINGS_GROUP); ?>
            <h2>Configuration</h2>
            <div>
                <p>To get started, you'll need to link this site with your Agent Monitor site.</p>
                <p style="font-weight: bold;">How to find your access token</p>
                <ol>
                    <li>Open your Agent Monitor dashboard in a new tab.</li>
                    <li>Go to the "Integrate" page.</li>
                    <li>Copy the Site Token provided.</li>
                    <li>Paste your token in the field below and save your changes.</li>
                </ol>
                <input
                    type="password"
                    placeholder="Site token"
                    id="<?php echo esc_attr(AGENT_MONITOR_TOKEN); ?>"
                    name="<?php echo esc_attr(AGENT_MONITOR_TOKEN); ?>"
                    value="<?php echo esc_attr(get_option(AGENT_MONITOR_TOKEN, '')); ?>"
                />
            </div>
            <div>
                <label class="field-label" for="<?php echo esc_attr( AGENT_MONITOR_LOG_MAX_SIZE ); ?>">
                    <?php esc_html_e( 'Maximum log file size', 'agent-monitor' ); ?>: <output id="agent-monitor-log-max-size-output"><?php echo esc_html( get_option( AGENT_MONITOR_LOG_MAX_SIZE, 32 ) ); ?></output> MB
                </label>
                <input
                    type="range"
                    id="<?php echo esc_attr( AGENT_MONITOR_LOG_MAX_SIZE ); ?>"
                    name="<?php echo esc_attr( AGENT_MONITOR_LOG_MAX_SIZE ); ?>"
                    min="16"
                    max="128"
                    step="8"
                    value="<?php echo esc_attr( get_option( AGENT_MONITOR_LOG_MAX_SIZE, 32 ) ); ?>"
                    oninput="document.getElementById('agent-monitor-log-max-size-output').value = this.value"
                />
            </div>
            <div class="card-footer">
                <?php submit_button('', 'primary', 'submit', false); ?>
            </div>
        </form>
    </div>
    <?php
}