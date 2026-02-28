<?php

/**
 * Agent Monitor for WordPress
 *
 * @package     Agent Monitor
 * @author      Agent Monitor
 * @copyright   2025 Top Online
 * @license     GPL-2.0
 *
 * @wordpress-plugin
 * Plugin Name:         Agent Monitor
 * Description:         Track AI Agents on Your Website
 * Version:             0.1.1
 * Requires at least:   6.8
 * Requires PHP:        7.0
 * Author:              Agent Monitor
 * Author URI:          https://agentmonitor.io/
 * Text Domain:         agent-monitor
 * License:             GPL-2.0
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AGENT_MONITOR_PLUGIN_FILE', __FILE__);
define('AGENT_MONITOR_PLUGIN_VERSION', '0.1.1');

require_once plugin_dir_path( __FILE__ ) . 'includes/constants.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/file-system.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/server-analytics.php';

register_activation_hook( __FILE__, 'agent_monitor_create_files_dir' );