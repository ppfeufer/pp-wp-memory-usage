<?php
/**
 * Plugin Name: WordPress Memory Usage
 * Plugin URI: https://github.com/ppfeufer/pp-wp-memory-usage
 * Description: Display the memory limit and current memory usage in the dashboard and admin footer
 * Version: 1.5.2
 * Author: H. Peter Pfeufer
 * Author URI: https://ppfeufer.de
 * License: GPLv2
 * Text Domain: pp-wp-memory-usage
 * Domain Path: /l10n
 */

//namespace WordPress\Ppfeufer\Plugin\WpMemoryUsage;

// phpcs:disable
use WordPress\Ppfeufer\Plugin\WpMemoryUsage\Main;

define('PP_WP_MEMORY_PLUGIN_DIR', plugin_dir_path(__FILE__));

//require_once trailingslashit(value: __DIR__) . 'vendor/autoload.php';
require_once trailingslashit(value: __DIR__) . 'Sources/autoloader.php';
// phpcs:enable

$plugin = new Main();
$plugin->init();
