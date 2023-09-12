<?php
/**
 * Plugin Name: WordPress Memory Usage
 * Plugin URI:
 * Description: Display the memory limit and current memory usage in the dashboard and admin footer
 * Version: 1.0.1
 * Author: H. Peter Pfeufer
 * Author URI: https://ppfeufer.de
 * License: GPLv2
 * Text Domain: pp-wp-memory-usage
 * Domain Path: /l10n
 */

namespace WordPress\Plugins\PpWpMemoryUsage;

require_once(trailingslashit(__DIR__) . 'Libs/YahnisElsts/PluginUpdateChecker/plugin-update-checker.php');

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class MemoryUsage {
    protected array $memory = [];

    public function init(): void {
        $this->loadTextDomain();
        $this->getMemoryLimit();
        $this->getMemoryUsage();
        $this->getMemoryPercentage();
        $this->doUpdateCheck();

        add_action('wp_dashboard_setup', [$this, 'addDashboardWidget']);
        add_filter('admin_footer_text', [$this, 'addFooter']);
    }

    /**
     * loading text domain
     */
    public function loadTextDomain(): void {
        if (function_exists('load_plugin_textdomain')) {
            load_plugin_textdomain('pp-wp-memory-usage', false, basename(__DIR__) . '/l10n/');
        }
    }

    public function doUpdateCheck(): void {
        /**
         * Check GitHub for updates
         */
        $myUpdateChecker = PucFactory::buildUpdateChecker(
            'https://github.com/ppfeufer/pp-wp-memory-usage/',
            __FILE__,
            'pp-wp-memory-usage'
        );

        $myUpdateChecker->getVcsApi()->enableReleaseAssets();
    }

    private function getMemoryLimit(): void {
        $memoryLimit = (int)ini_get('memory_limit');

        $this->memory['limit'] = (empty($memoryLimit))
            ? __('N/A', 'pp-wp-memory-usage')
            : $memoryLimit . __(' MB', 'pp-wp-memory-usage');
    }

    private function getMemoryUsage(): void {
        $memoryUsage = 0;

        if (function_exists('memory_get_usage')) {
            $memoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
        }

        $this->memory['usage'] = (empty($memoryUsage))
            ? __('N/A', 'pp-wp-memory-usage')
            : $memoryUsage . __(' MB', 'pp-wp-memory-usage');
    }

    private function getMemoryPercentage(): void {
        if (!empty($this->memory['usage']) && !empty($this->memory['limit'])) {
            $this->memory['percent'] = round(
                trim(str_ireplace(' MB', '', $this->memory['usage'])) / trim(str_ireplace(' MB', '', $this->memory['limit'])) * 100
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

    public function renderDashboardWidget(): void {
        ?>
        <ul>
            <li>
                <strong><?php _e('PHP Version', 'pp-wp-memory-usage'); ?>:</strong>
                <span><?php echo PHP_VERSION; ?>&nbsp;/&nbsp;<?php echo sprintf(__('%1$s Bit OS', 'pp-wp-memory-usage'), PHP_INT_SIZE * 8); ?></span>
            </li>
            <li>
                <strong><?php _e('Memory Limit', 'pp-wp-memory-usage'); ?>:</strong>
                <span><?php echo $this->memory['limit']; ?></span>
            </li>
            <li>
                <strong><?php _e('Memory Usage', 'pp-wp-memory-usage'); ?>:</strong>
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

    public function addDashboardWidget(): void {
        wp_add_dashboard_widget(
            'pp_memory_dashboard',
            __('Memory Usage Overview', 'pp-wp-memory-usage'),
            [$this, 'renderDashboardWidget']
        );
    }

    public function addFooter(string $content): string {
        $content .= ' | ' . sprintf(__(
                'Memory Usage: %1$s of %2$s (%3$s%%)',
                'pp-wp-memory-usage'
            ),
            $this->memory['usage'],
            $this->memory['limit'],
            $this->memory['percent']
        );

        return $content;
    }
}

/**
 * Start the plugin, only in backend
 */
function initialize_plugin(): void {
    $memoryUsage = new MemoryUsage;

    $memoryUsage->init();
}

if (is_admin()) {
    initialize_plugin();
}
