<?php
/**
 * Plugin Name:       Lazy Load Comments
 * Plugin URI:        https://github.com/Azanumer/wp-lazy-load-comments
 * Description:       Defers WordPress comments until the reader scrolls to them — comments load via the REST API, so long threads stop slowing the initial page load.
 * Version:           1.0.0
 * Author:            Azan Umer
 * License:           MIT
 * Text Domain:       wllc
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WLLC_VERSION', '1.0.0' );

/**
 * Replace the theme's comments template with a lightweight placeholder.
 * The placeholder is swapped for real comments by inline JS when scrolled into view.
 */
function wllc_placeholder( $file ) {
    if ( is_singular() && comments_open() ) {
        return __DIR__ . '/comments-placeholder.php';
    }
    return $file;
}
add_filter( 'comments_template', 'wllc_placeholder' );

/**
 * Register the REST route that returns rendered comments for a post.
 * Public read: only published posts, and only their approved comments are rendered.
 */
function wllc_register_route() {
    register_rest_route( 'wllc/v1', '/comments/(?P<id>\d+)', array(
        'methods'             => 'GET',
        'callback'            => 'wllc_render_comments',
        'permission_callback' => '__return_true',
        'args'                => array(
            'id' => array( 'validate_callback' => function ( $value ) {
                return (int) $value > 0;
            } ),
        ),
    ) );
}
add_action( 'rest_api_init', 'wllc_register_route' );

function wllc_render_comments( WP_REST_Request $request ) {
    $post_id = (int) $request->get_param( 'id' );
    $post    = get_post( $post_id );

    if ( ! $post || 'publish' !== $post->post_status || ! comments_open( $post ) ) {
        return new WP_Error( 'wllc_not_found', 'Comments are not available for this post.', array( 'status' => 404 ) );
    }

    // Render through the theme's own comments template, but only for this request.
    remove_filter( 'comments_template', 'wllc_placeholder' );
    ob_start();
    comments_template( '', true );
    $html = ob_get_clean();
    add_filter( 'comments_template', 'wllc_placeholder' );

    return rest_ensure_response( array( 'html' => $html ) );
}

/** Enqueue the tiny loader script (no libraries, IntersectionObserver + fetch). */
function wllc_enqueue() {
    if ( ! is_singular() || ! comments_open() ) {
        return;
    }
    wp_enqueue_script(
        'wllc-loader',
        plugins_url( 'lazy-load-comments.js', __FILE__ ),
        array(),
        WLLC_VERSION,
        true
    );
    wp_localize_script( 'wllc-loader', 'wllcData', array(
        'restUrl' => esc_url_raw( rest_url( 'wllc/v1/comments/' ) ),
        'postId'  => get_the_ID(),
    ) );
}
add_action( 'wp_enqueue_scripts', 'wllc_enqueue' );
