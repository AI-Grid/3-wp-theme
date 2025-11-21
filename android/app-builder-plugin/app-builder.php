<?php
/**
 * Plugin Name: Android WebView Builder
 * Description: Build ready-to-publish WebView APK/AAB bundles with signing, keystore generation, and SDK refresh utilities.
 * Version: 1.0.0
 * Author: Project Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('ANDROID_APP_BUILDER_PATH')) {
    define('ANDROID_APP_BUILDER_PATH', plugin_dir_path(__FILE__));
}

require_once ANDROID_APP_BUILDER_PATH . 'includes/class-app-builder-admin.php';

function android_app_builder_bootstrap() {
    $admin = new App_Builder_Admin(ANDROID_APP_BUILDER_PATH);
    $admin->hooks();
}
add_action('plugins_loaded', 'android_app_builder_bootstrap');
