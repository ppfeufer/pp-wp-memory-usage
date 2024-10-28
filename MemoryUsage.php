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
define(
    constant_name: __NAMESPACE__ . '\PLUGIN_DIR',
    value: plugin_dir_path(file: __FILE__)
);

require_once trailingslashit(value: __DIR__) . 'Sources/autoloader.php';
require_once trailingslashit(value: __DIR__) . 'Sources/Libs/autoload.php';
// phpcs:enable

// Load the plugin's main class.
(new Main())->init();
