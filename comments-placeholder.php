<?php
/**
 * Lightweight placeholder shown until comments are lazy-loaded.
 * Kept dependency-free: the loader script swaps this div for real comments.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="wllc-comments-placeholder" class="wllc-placeholder">
    <p><?php esc_html_e( 'Loading comments…', 'wllc' ); ?></p>
</div>
<noscript>
    <?php
    // No-JS fallback: render the theme's comments template the normal way.
    remove_filter( 'comments_template', 'wllc_placeholder' );
    comments_template();
    ?>
</noscript>
