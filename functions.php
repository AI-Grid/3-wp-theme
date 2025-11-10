<?php
defined( 'ABSPATH' ) || die( 'Sorry, but you cannot access this page directly.' );
define( 'REDMONDBASE', __DIR__ );
define( 'RTEXTDOMAIN', 'redmond-inspired' );
define( 'REDMONDURI', get_template_directory_uri() );

if ( ! defined( 'REDMOND_THEME_VERSION' ) ) {
	define( 'REDMOND_THEME_VERSION', '1.0.0' );
}


function redmond_nr_function( $function, $data = null ) {
	if ( extension_loaded( 'newrelic' ) && substr( $function, 0 , 9 ) == 'newrelic_' ) {
		switch ( true ) {
			case is_array( $data ):
				call_user_func_array( $function , $data );
				break;
			case is_null( $data ):
				call_user_func( $function );
				break;
			default:
				call_user_func( $function , $data );
				break;
		}
	}
}

function redmond_wp_print_r( $ar, $print = false ) {
	$string = '<pre>' . htmlentities( print_r( $ar , true ) ) . '</pre>';
	if ( true == $print ) {
		wp_die( esc_html( $string ) , esc_html__( 'Feedback',RTEXTDOMAIN ) , array(
			'response' => 200,
		) );
	}
	else {
		return $string;
	}
}

if ( extension_loaded( 'newrelic' ) && true === get_option( 'redmond_use_newrelic_error_logger', false ) ) {
	set_error_handler( 'newrelic_notice_error' );
}


$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( REDMONDBASE . '/bin' ), RecursiveIteratorIterator::SELF_FIRST );
foreach ( $objects as $name => $obj ) {
	if ( substr( $name, -4 ) == '.php' && strpos( $name, 'index.php' ) === false ) {
		require_once $name;
	}
}

/**
 * Add and Remove Actions Directly from the Functions Page
 */
add_action( 'after_setup_theme', 'redmond_remove_admin_bar' );
add_action( 'after_setup_theme', 'redmond_add_theme_supports' );
add_action( 'customize_register', 'redmond_customize_register' );
add_action( 'after_setup_theme', 'redmond_add_menus' );
add_action( 'wp_enqueue_scripts', 'redmond_theme_add_scripts_and_styles' );
add_action( 'wp_head', 'redmond_do_site_icons' );
add_action( 'wp_head', 'redmond_add_custom_start_menu_styles' );
if ( extension_loaded( 'newrelic' ) ) {
	add_action( 'wp_head', 'redmond_new_relic_timing_header' );
	add_action( 'wp_footer', 'redmond_new_relic_timing_footer' );
}

add_action( 'wp_ajax_getpost','redmond_getpost_callback' );
add_action( 'wp_ajax_nopriv_getpost','redmond_getpost_callback' );
add_action( 'wp_ajax_getarchive','redmond_getarchive_callback' );
add_action( 'wp_ajax_nopriv_getarchive','redmond_getarchive_callback' );
add_action( 'wp_ajax_getsearch','redmond_getsearch_callback' );
add_action( 'wp_ajax_nopriv_getsearch','redmond_getsearch_callback' );
add_action( 'wp_ajax_getauthor','redmond_getauthor_callback' );
add_action( 'wp_ajax_nopriv_getauthor','redmond_getauthor_callback' );
add_action( 'wp_ajax_redmond_get_comment_form', 'redmond_get_comment_form_callback' );
add_action( 'wp_ajax_nopriv_redmond_get_comment_form', 'redmond_get_comment_form_callback' );
add_action( 'wp_ajax_redmond_get_share_data', 'redmond_share_post_callback' );
add_action( 'wp_ajax_nopriv_redmond_get_share_data', 'redmond_share_post_callback' );
add_action( 'wp_footer','redmond_set_info_cookies' );
add_action( 'wp_enqueue_scripts', 'redmond_maybe_enqueue_comment_reply' );

$redmond_head_cleanup = array(
	'rsd_link',
	'wlwmanifest_link',
	'index_rel_link',
	'parent_post_rel_link',
	'parent_post_rel_link_wp_head',
	'start_post_rel_link',
	'start_post_rel_link_wp_head',
	'adjacent_posts_rel_link_wp_head',
	'wp_generator',
);

foreach ( $redmond_head_cleanup as $redmond_head_action ) {
        remove_action( 'wp_head', $redmond_head_action );
}

function redmond_maybe_enqueue_comment_reply() {
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
                wp_enqueue_script( 'comment-reply' );
        }
}

?>