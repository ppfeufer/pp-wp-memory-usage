<?php
/**
 * WordPress Memory Usage
 *
 * Display the memory limit and current memory usage in the dashboard and admin footer
 *
 * @package WordPress\Ppfeufer\Plugin\WordPressTweaks
 * @author H. Peter Pfeufer
 * @copyright 2021 H. Peter Pfeufer
 * @license GPL-3.0-or-later
 * @version 1.6.3
 *
 * @wordpress-plugin
 * Plugin Name: WordPress Memory Usage
 * Plugin URI: https://github.com/ppfeufer/pp-wp-memory-usage
 * Description: Display the memory limit and current memory usage in the dashboard and admin footer
 * Version: 1.6.3
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Author: H. Peter Pfeufer
 * Author URI: https://ppfeufer.de
 * Text Domain: pp-wp-memory-usage
 * Domain Path: /l10n
 * License: GPLv3
 * License URI: https://github.com/ppfeufer/pp-wp-memory-usage/blob/master/LICENSE
 */

namespace WordPress\Ppfeufer\Plugin\WpMemoryUsage;

// phpcs:disable
// Plugin directory path
define(
    constant_name: __NAMESPACE__ . '\PLUGIN_DIR_PATH',
    value: plugin_dir_path(file: __FILE__)
);

// Plugin directory relative path
define(
    constant_name: __NAMESPACE__ . '\PLUGIN_REL_PATH',
    value: dirname(plugin_basename(__FILE__))
);

// Include the autoloader and the libraries autoloader
require_once PLUGIN_DIR_PATH . 'Sources/autoloader.php';
require_once PLUGIN_DIR_PATH . 'Sources/Libs/autoload.php';
// phpcs:enable

// Load the plugin's main class.
new Main();
