<?php

/**
 * Agent Monitor for WordPress
 *
 * @package     Agent Monitor
 * @author      Agent Monitor
 * @copyright   2026 Top Online
 * @license     GPL-2.0
 *
 * @wordpress-plugin
 * Plugin Name:         Agent Monitor - Monitor AI Agents and analyze LLM visibility
 * Description:         Track LLM assistants, crawlers and autonomous agents visiting your site. Analyze LLM visibility.
 * Version:             1.0.0
 * Requires at least:   5.0
 * Tested up to:        7.0
 * Requires PHP:        7.0
 * Author:              Agent Monitor
 * Author URI:          https://agentmonitor.io/
 * Text Domain:         agent-monitor
 * License:             GPL-2.0
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/constants.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/file-system.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/server-analytics.php';

register_activation_hook( __FILE__, 'agent_monitor_create_files_dir' );