<?php
/**
 * Dashboard Widget Template
 *
 * @package    WordPress\Ppfeufer\Plugin\WpMemoryUsage
 * @subpackage Sources/Templates
 */

/**
 * Arguments passed to the template
 *
 * @var array $args The arguments passed to the template
 */
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

        <span><?php echo $args['memory']['limit']; ?></span>
    </li>

    <li>
        <strong>
            <?php _e('Memory Usage', 'pp-wp-memory-usage'); ?>:
        </strong>

        <span><?php echo $args['memory']['usage']; ?></span>
    </li>
</ul>

<?php
if (!empty($args['memory']['percent'])) {
    ?>
    <div class="progressbar">
        <div
            style="border: 1px solid rgb(223 223 223); background-color: rgb(249 249 249); box-shadow: 0 1px 0 rgb(255 255 255) inset; border-radius: 3px;"
        >
            <div
                class="button-primary"
                style="width: <?php echo $args['memory']['percent']; ?>%; <?php echo $args['memory']['color']; ?> padding: 0; border-width: 0; color: rgb(255 255 255); text-align: right; border-color: rgb(223 223 223); box-shadow: 0 1px 0 rgb(255 255 255) inset; border-radius: 3px; margin-top: -1px; cursor: default;"
            >
                <div style="padding: 2px; <?php echo $args['memory']['percent_pos']; ?>"><?php echo $args['memory']['percent']; ?>
                    %
                </div>
            </div>
        </div>
    </div>
    <?php
}
