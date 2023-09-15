<?php
/**
 * Plugin Name: WordPress Memory Usage
 * Plugin URI:
 * Description: Display the memory limit and current memory usage in the dashboard and admin footer
 * Version: 1.2.0
 * Author: H. Peter Pfeufer
 * Author URI: https://ppfeufer.de
 * License: GPLv2
 * Text Domain: pp-wp-memory-usage
 * Domain Path: /l10n
 */

namespace WordPress\Plugins\PpWpMemoryUsage;

require_once(
    trailingslashit(value: __DIR__) . 'Libs/YahnisElsts/PluginUpdateChecker/plugin-update-checker.php'
);

use WordPress\Plugins\PpWpMemoryUsage\Libs\YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class MemoryUsage {
    /**
     * @var array
     */
    protected array $memory = [];

    /**
     * Initialize the class
     *
     * @return void
     */
    public function init(): void {
        $this->loadTextDomain();
        $this->getMemoryLimit();
        $this->getMemoryUsage();
        $this->getMemoryPercentage();
        $this->doUpdateCheck();

        add_action(
            hook_name: 'wp_dashboard_setup', callback: [$this, 'addDashboardWidget']
        );
        add_filter(hook_name: 'admin_footer_text', callback: [$this, 'addFooter']);
    }

    /**
     * Loading the text domain
     *
     * @return void
     */
    public function loadTextDomain(): void {
        if (function_exists('load_plugin_textdomain')) {
            load_plugin_textdomain(
                domain: 'pp-wp-memory-usage',
                plugin_rel_path: basename(path: __DIR__) . '/l10n/'
            );
        }
    }

    /**
     * Check GitHub for updates
     *
     * @return void
     */
    public function doUpdateCheck(): void {
        $myUpdateChecker = PucFactory::buildUpdateChecker(
            metadataUrl: 'https://github.com/ppfeufer/pp-wp-memory-usage/',
            fullPath: __FILE__,
            slug: 'pp-wp-memory-usage'
        );

        $myUpdateChecker->getVcsApi()->enableReleaseAssets();
    }

    /**
     * Get memory limit
     *
     * @return void
     */
    private function getMemoryLimit(): void {
        $memoryLimit = (int) ini_get(option: 'memory_limit');

        $this->memory['limit'] = (empty($memoryLimit))
            ? __(text: 'N/A', domain: 'pp-wp-memory-usage')
            : $memoryLimit . __(text: ' MB', domain: 'pp-wp-memory-usage');
    }

    /**
     * Get memory usage
     *
     * @return void
     */
    private function getMemoryUsage(): void {
        $memoryUsage = 0;

        if (function_exists(function: 'memory_get_usage')) {
            $memoryUsage = round(num: memory_get_usage() / 1024 / 1024, precision: 2);
        }

        $this->memory['usage'] = (empty($memoryUsage))
            ? __(text: 'N/A', domain: 'pp-wp-memory-usage')
            : $memoryUsage . __(text: ' MB', domain: 'pp-wp-memory-usage');
    }

    /**
     * Get memory percentage
     *
     * @return void
     */
    private function getMemoryPercentage(): void {
        if (!empty($this->memory['usage']) && !empty($this->memory['limit'])) {
            $this->memory['percent'] = round(
                num: trim(
                    str_ireplace(
                        search: ' MB', replace: '', subject: $this->memory['usage']
                    )
                ) / trim(
                    str_ireplace(
                        search: ' MB', replace: '', subject: $this->memory['limit']
                    )
                ) * 100
            );

            /**
             * If the bar is too small, we move the text outside
             */
            $this->memory['percent_pos'] = '';

            /**
             * In case we are in our limits, take the admin color
             */
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
     * Render the dashboard widget
     *
     * @return void
     */
    public function renderDashboardWidget(): void {
        ?>
        <ul>
            <li>
                <strong><?php _e(text: 'PHP Version', domain: 'pp-wp-memory-usage'); ?>:</strong>
                <span>
                    <?php echo PHP_VERSION; ?>
                    /
                    <?php
                    echo sprintf(
                        __(text: '%1$s Bit OS', domain: 'pp-wp-memory-usage'),
                        PHP_INT_SIZE * 8
                    );
                    ?>
                </span>
            </li>
            <li>
                <strong>
                    <?php _e(text: 'Memory Limit', domain: 'pp-wp-memory-usage'); ?>:
                </strong>
                <span><?php echo $this->memory['limit']; ?></span>
            </li>
            <li>
                <strong>
                    <?php _e(text: 'Memory Usage', domain: 'pp-wp-memory-usage'); ?>:
                </strong>
                <span><?php echo $this->memory['usage']; ?></span>
            </li>
        </ul>
        <?php
        if (!empty($this->memory['percent'])) {
            ?>
            <div class="progressbar">
                <div style="border:1px solid rgb(223 223 223); background-color: rgb(249 249 249); box-shadow: 0 1px 0 rgb(255 255 255) inset; border-radius: 3px;">
                    <div class="button-primary" style="width: <?php echo $this->memory['percent']; ?>%; <?php echo $this->memory['color']; ?> padding: 0; border-width: 0; color: rgb(255 255 255); text-align: right; border-color: rgb(223 223 223); box-shadow: 0 1px 0 rgb(255 255 255) inset; border-radius: 3px; margin-top: -1px; cursor: default;">
                        <div style="padding: 2px; <?php echo $this->memory['percent_pos']; ?>"><?php echo $this->memory['percent']; ?>%</div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Add the widget to the dashboard
     *
     * @return void
     */
    public function addDashboardWidget(): void {
        wp_add_dashboard_widget(
            widget_id: 'pp_memory_dashboard',
            widget_name: __(
                text: 'Memory Usage Overview', domain: 'pp-wp-memory-usage'
            ),
            callback: [$this, 'renderDashboardWidget']
        );
    }

    /**
     * Add some text to the admin footer
     *
     * @param string $content
     * @return string
     */
    public function addFooter(string $content): string {
        $content .= ' | ' . sprintf(__(
                text: 'Memory Usage: %1$s of %2$s (%3$s%%)',
                domain: 'pp-wp-memory-usage'
            ),
            $this->memory['usage'],
            $this->memory['limit'],
            $this->memory['percent']
        );

        return $content;
    }
}

// Only initialize the plugin when the user is in the admin backend
if (is_admin()) {
    /**
     * Start the plugin, only in backend
     *
     * @return void
     */
    function initialize_plugin(): void {
        $memoryUsage = new MemoryUsage;

        $memoryUsage->init();
    }

    initialize_plugin();
}
