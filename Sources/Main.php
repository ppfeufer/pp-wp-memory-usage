<?php

namespace WordPress\Ppfeufer\Plugin\WpMemoryUsage;

use Exception;
use RuntimeException;
use WordPress\Ppfeufer\Plugin\WpMemoryUsage\Libs\YahnisElsts\PluginUpdateChecker\v5p5\PucFactory;
use WP_Admin_Bar;

/**
 * Main class
 *
 * @package WordPress\Ppfeufer\Plugin\WpMemoryUsage
 */
class Main {
    /**
     * @var array $memory The memory data
     * @access protected
     */
    protected array $memory = [];

    /**
     * Main constructor
     *
     * @access public
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the class
     *
     * @return void
     * @access public
     */
    public function init(): void {
        $this->getMemoryLimit();
        $this->getMemoryUsage();
        $this->getMemoryPercentage();
        $this->doUpdateCheck();
        $this->initializeHooks();
    }

    /**
     * Get memory limit
     *
     * @return void
     * @access private
     */
    private function getMemoryLimit(): void {
        $memoryLimit = (int)ini_get(option: 'memory_limit');

        $this->memory['limit'] = (empty($memoryLimit))
            ? __('N/A', 'pp-wp-memory-usage')
            : $memoryLimit . __(' MB', 'pp-wp-memory-usage');
    }

    /**
     * Get memory usage
     *
     * @return void
     * @access private
     */
    private function getMemoryUsage(): void {
        $memoryUsage = 0;

        if (function_exists(function: 'memory_get_usage')) {
            $memoryUsage = round(num: memory_get_usage() / 1024 / 1024, precision: 2);
        }

        $this->memory['usage'] = (empty($memoryUsage))
            ? __('N/A', 'pp-wp-memory-usage')
            : $memoryUsage . __(' MB', 'pp-wp-memory-usage');
    }

    /**
     * Get memory percentage
     *
     * @return void
     * @access private
     */
    private function getMemoryPercentage(): void {
        if (!empty($this->memory['usage']) && !empty($this->memory['limit'])) {
            $this->memory['percent'] = round(
                num: trim(
                    str_ireplace(
                        search: ' MB',
                        replace: '',
                        subject: $this->memory['usage']
                    )
                ) / trim(
                    str_ireplace(
                        search: ' MB',
                        replace: '',
                        subject: $this->memory['limit']
                    )
                ) * 100
            );

            // If the bar is too small, we move the text outside.
            $this->memory['percent_pos'] = '';

            // In case we are in our limits, take the admin color.
            $this->memory['color'] = '';

            if ($this->memory['percent'] > 80) {
                $this->memory['color'] = 'background: rgb(230 111 0);';
            }

            if ($this->memory['percent'] > 95) {
                $this->memory['color'] = 'background: rgb(255 0 0);';
            }

            if ($this->memory['percent'] < 10) {
                $this->memory['percent_pos'] = 'margin-right: -30px; color: rgb(68 68 68);';
            }
        }
    }

    /**
     * Check GitHub for updates
     *
     * @return void
     * @access public
     */
    public function doUpdateCheck(): void {
        PucFactory::buildUpdateChecker(
            metadataUrl: 'https://github.com/ppfeufer/pp-wp-memory-usage/',
            fullPath: PLUGIN_DIR_PATH . 'MemoryUsage.php',
            slug: 'pp-wp-memory-usage'
        )->getVcsApi()->enableReleaseAssets();
    }

    /**
     * Initialize the hooks
     *
     * @return void
     * @access private
     */
    private function initializeHooks(): void {
        // Load the text domain.
        add_action(hook_name: 'init', callback: static function () {
            load_plugin_textdomain(
                domain: 'pp-wp-memory-usage',
                plugin_rel_path: PLUGIN_REL_PATH . '/l10n/'
            );
        });

        // Add the admin bar info
        add_action(
            hook_name: 'admin_bar_menu',
            callback: [$this, 'addAdminBarInfo'],
            priority: 999
        );

        // Add the admin hooks
        if (is_admin()) {
            // Add the dashboard widget
            add_action(
                hook_name: 'wp_dashboard_setup',
                callback: [$this, 'addDashboardWidget']
            );

            // Add the footer text
            add_filter(hook_name: 'admin_footer_text', callback: [$this, 'addFooter']);
        }
    }

    /**
     * Render the dashboard widget
     *
     * @return void
     * @access public
     */
    public function renderDashboardWidget(): void {
        $templateFile = 'Sources/Templates/dashboard-widget.php';

        $this->loadTemplate($templateFile, ['memory' => $this->memory]);
    }

    /**
     * Load a plugin template file
     *
     * Usage:
     * ```
     * $args = ['var' => $this->var];
     * $templateFile = 'Sources/Templates/templatefile.php';
     * $this->loadTemplate($templateFile, $args);
     * ```
     *
     * @param string $templateFile The template file to load (Relative to the plugin directory)
     * @param array $args The arguments to pass to the template. Available in the template as `$args`
     * @return void
     * @access private
     */
    private function loadTemplate(string $templateFile, array $args = []): void {
        try {
            if (file_exists(filename: PLUGIN_DIR_PATH . $templateFile)) {
                load_template(
                    _template_file: PLUGIN_DIR_PATH . $templateFile,
                    args: $args
                );
            } else {
                throw new RuntimeException(
                    message: "Template file for dashboard widget not found at {$templateFile}"
                );
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Add the widget to the dashboard
     *
     * @return void
     * @access public
     */
    public function addDashboardWidget(): void {
        wp_add_dashboard_widget(
            widget_id: 'pp_memory_dashboard',
            widget_name: __(
                'Memory Usage Overview',
                'pp-wp-memory-usage'
            ),
            callback: [$this, 'renderDashboardWidget']
        );
    }

    /**
     * Add some text to the admin footer
     *
     * @param string $content The current footer content
     * @return string
     * @access public
     */
    public function addFooter(string $content): string {
        $content .= ' | ' . $this->getMemoryUsageString();

        return $content;
    }

    /**
     * Get the memory usage string
     *
     * @return string
     * @access private
     */
    private function getMemoryUsageString(): string {
        return sprintf(
        /* Translators: %1$s: Current memory usage, %2$s: Memory limit, %3$s: Current memory usage percentage. */
            __(
                'Memory Usage: %1$s of %2$s (%3$s%%)',
                'pp-wp-memory-usage'
            ),
            $this->memory['usage'],
            $this->memory['limit'],
            $this->memory['percent']
        );
    }

    /**
     * Add some text to the admin bar
     *
     * @param WP_Admin_Bar $wpAdminBar The admin bar instance
     * @return void
     * @access public
     */
    public function addAdminBarInfo(WP_Admin_Bar $wpAdminBar): void {
        $wpAdminBar->add_node(
            [
                'id' => 'memory_usage',
                'title' => $this->getMemoryUsageString(),
                'parent' => 'top-secondary',
                'meta' => [
                    'class' => 'memory-usage',
                ],
            ]
        );
    }
}
