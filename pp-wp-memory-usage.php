<?php
/**
 * Plugin Name: WordPress Memory Usage
 * Plugin URI:
 * Description: Show up the memory limit and current memory usage in the dashboard and admin footer
 * Version: 1.0.0
 * Author: H.-Peter Pfeufer
 * Author URI: http://ppfeufer.de
 * License: GPLv2
 */

namespace WordPress\Plugins\PpWpMemoryUsage;

class MemoryUsage {
    protected $memory = null;

    /**
     * Textdomain
     *
     * @var string
     */
    private $textDomain = null;

    /**
     * Localization Directory
     *
     * @var string
     */
    private $localizationDirectory = null;

    public function __construct() {
        /**
         * Initializing Variables
         */
        $this->memory = array();
        $this->textDomain = 'pp-wp-basic-security';
        $this->localizationDirectory = \basename(\dirname(__FILE__)) . '/l10n/';
    }

    public function init() {

        $this->getMemoryLimit();
        $this->getMemoryUsage();
        $this->getMemoryPercentage();

        \add_action('wp_dashboard_setup', array($this, 'addDashboardWidget'));
        \add_filter('admin_footer_text', array($this, 'addFooter'));
    }

    private function getMemoryLimit() {
        $memoryLimit = (int) \ini_get('memory_limit');

        $this->memory['limit'] = (empty($memoryLimit)) ? \__('N/A') : $memoryLimit . \__(' MB');
    }

    private function getMemoryUsage() {
        $memoryUsage = 0;

        if(\function_exists('\memory_get_usage')) {
            $memoryUsage = \round(\memory_get_usage() / 1024 / 1024, 2);
        }

        $this->memory['usage'] = (empty($memoryUsage)) ? \__('N/A') : $memoryUsage . \__(' MB');
    }

    private function getMemoryPercentage() {
        if(!empty($this->memory['usage']) && !empty($this->memory['limit'])) {
            $this->memory['percent'] = \round(\trim(\str_ireplace(' MB', '', $this->memory['usage'])) / \trim(\str_ireplace(' MB', '', $this->memory['limit'])) * 100, 0);

            /**
             * If the bar is too small we move the text outside
             */
            $this->memory['percent_pos'] = '';

            /**
             * In case we are in our limits take the admin color
             */
            $this->memory['color'] = '';

            if($this->memory['percent'] > 80) {
                $this->memory['color'] = 'background: #E66F00;';
            }

            if($this->memory['percent'] > 95) {
                $this->memory['color'] = 'background: red;';
            }

            if($this->memory['percent'] < 10) {
                $this->memory['percent_pos'] = 'margin-right: -30px; color: #444;';
            }
        }
    }

    public function renderDashboardWidget() {
        ?>
        <ul>
            <li><strong><?php \_e('PHP Version'); ?></strong> : <span><?php echo \PHP_VERSION; ?>&nbsp;/&nbsp;<?php echo \sprintf(\__('%1$s Bit OS'), \PHP_INT_SIZE * 8); ?></span></li>
            <li><strong><?php \_e('Memory Limit'); ?></strong> : <span><?php echo $this->memory['limit']; ?></span></li>
            <li><strong><?php \_e('Memory Usage'); ?></strong> : <span><?php echo $this->memory['usage']; ?></span></li>
        </ul>
        <?php
        if(!empty($this->memory['percent'])) {
            ?>
            <div class="progressbar">
                <div style="border:1px solid #DDDDDD; background-color:#F9F9F9;	border-color: rgb(223, 223, 223); box-shadow: 0px 1px 0px rgb(255, 255, 255) inset; border-radius: 3px;">
                    <div class="button-primary" style="width: <?php echo $this->memory['percent']; ?>%;<?php echo $this->memory['color']; ?>padding: 0px;border-width:0px; color:#FFFFFF;text-align:right; border-color: rgb(223, 223, 223); box-shadow: 0px 1px 0px rgb(255, 255, 255) inset; border-radius: 3px; margin-top: -1px; cursor: default;">
                        <div style="padding:2px;<?php echo $this->memory['percent_pos']; ?>"><?php echo $this->memory['percent']; ?>%</div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function addDashboardWidget() {
        \wp_add_dashboard_widget('pp_memory_dashboard', \__('Memory Usage Overview'), array($this, 'renderDashboardWidget'));
    }

    public function addFooter($content) {
        $content .= \sprintf(\__(' | Memory Usage: %1$s of %2$s (%3$s%%)'),
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
function intializePlugin() {
    $memoryUsage = new \WordPress\Plugins\PpWpMemoryUsage\MemoryUsage;

    $memoryUsage->init();
}

if(\is_admin()) {
    intializePlugin();
}
