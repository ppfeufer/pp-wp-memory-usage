<?php

namespace WordPress\Ppfeufer\Plugin\WpMemoryUsage;

use WordPress\Ppfeufer\Plugin\WpMemoryUsage\Libs\YahnisElsts\PluginUpdateChecker\v5p4\PucFactory;
use WP_Admin_Bar;

class Main {
    /**
     * @var array $memory The memory data
     * @since 1.0.0
     * @access protected
     */
    protected array $memory = [];

    /**
     * Initialize the class
     *
     * @return void
     * @since 1.0.0
     * @access public
     */
    public function init(): void {
        $this->loadTextDomain();
        $this->getMemoryLimit();
        $this->getMemoryUsage();
        $this->getMemoryPercentage();
        $this->doUpdateCheck();

        if (is_admin()) {
            $this->addAdminHooks();
        }

        add_action(
            hook_name: 'admin_bar_menu',
            callback: [$this, 'addAdminBarInfo'],
            priority: 999
        );
    }

    /**
     * Loading the text domain
     *
     * @return void
     * @since 1.0.0
     * @access public
     */
    public function loadTextDomain(): void {
        if (function_exists(function: 'load_plugin_textdomain')) {
            load_plugin_textdomain(
                domain: 'pp-wp-memory-usage',
                plugin_rel_path: basename(path: __DIR__) . '/l10n/'
            );
        }
    }

    /**
     * Get memory limit
     *
     * @return void
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * Check GitHub for updates
     *
     * @return void
     * @since 1.0.0
     * @access public
     */
    public function doUpdateCheck(): void {
        PucFactory::buildUpdateChecker(
            metadataUrl: 'https://github.com/ppfeufer/pp-wp-memory-usage/',
            fullPath: PP_WP_MEMORY_PLUGIN_DIR . 'MemoryUsage.php',
            slug: 'pp-wp-memory-usage'
        )->getVcsApi()->enableReleaseAssets();
    }

    /**
     * Add the admin hooks
     *
     * @return void
     * @since 1.0.0
     * @access private
     */
    private function addAdminHooks(): void {
        add_action(
            hook_name: 'wp_dashboard_setup',
            callback: [$this, 'addDashboardWidget']
        );
        add_filter(hook_name: 'admin_footer_text', callback: [$this, 'addFooter']);
    }

    /**
     * Render the dashboard widget
     *
     * @return void
     * @since 1.0.0
     * @access public
     */
    public function renderDashboardWidget(): void {
        ?>
        <ul>
            <li>
                <strong>
                    <?php _e('PHP Version', 'pp-wp-memory-usage'); ?>:
                </strong>

                <span>
                    <?php echo PHP_VERSION; ?>
                    /
                    <?php
                    echo sprintf(
                    /* Translators: %1$s is the bit size of the operating system (32 or 64-bit) */
                        __('%1$s Bit Operating System', 'pp-wp-memory-usage'),
                        PHP_INT_SIZE * 8
                    );
                    ?>
                </span>
            </li>

            <li>
                <strong>
                    <?php _e('Memory Limit', 'pp-wp-memory-usage'); ?>:
                </strong>

                <span><?php echo $this->memory['limit']; ?></span>
            </li>

            <li>
                <strong>
                    <?php _e('Memory Usage', 'pp-wp-memory-usage'); ?>:
                </strong>

                <span><?php echo $this->memory['usage']; ?></span>
            </li>
        </ul>

        <?php
        if (!empty($this->memory['percent'])) {
            ?>
            <div class="progressbar">
                <div style="border: 1px solid rgb(223 223 223); background-color: rgb(249 249 249); box-shadow: 0 1px 0 rgb(255 255 255) inset; border-radius: 3px;">
                    <div
                        class="button-primary"
                        style="width: <?php echo $this->memory['percent']; ?>%; <?php echo $this->memory['color']; ?> padding: 0; border-width: 0; color: rgb(255 255 255); text-align: right; border-color: rgb(223 223 223); box-shadow: 0 1px 0 rgb(255 255 255) inset; border-radius: 3px; margin-top: -1px; cursor: default;"
                    >
                        <div style="padding: 2px; <?php echo $this->memory['percent_pos']; ?>"><?php echo $this->memory['percent']; ?>
                            %
                        </div>
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.4.1
     * @access private
     */
    private function getMemoryUsageString(): string {
        return sprintf(
        /* Translators: %1$s: Current memory usage, %2$s: Memory limit, %3$s: Current memory usage percentage */
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
     * @since 1.4.1
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
