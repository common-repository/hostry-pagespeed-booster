<?php
/*
Plugin Name: Hostry PageSpeed Booster
Description: Speed your website up and improve SEO ranking  as well as WPO rates by using CDN and  CSS, JavaScript, HTML minifications
Author: HOSTRY.com
Author URI: https://hostry.com/
License: GPLv2 or later
Version: 1.2.5
*/

/**
 *
 */
if ( ! defined('WPINC')) {
    die();
}

/**
 * Loading library default for plugin
 */
$plugin_lib = __DIR__ . '/includes';

$plugins = array(
    'main/CdnMinifierPlugin.php',
    'main/CdnMinifierRewriter.php',
    'main/CdnMinifierMinifier.php',
    'main/helpers.php'
);
foreach ($plugins as $plugin) {
    require_once $plugin_lib . '/' . $plugin;
}

/**
 * Loading additional library
 */
if (version_compare(phpversion(), '7.0.0') >= 0) {
    require_once $plugin_lib . '/vendor/autoload.php';
}
$libsPathClass = array(
    'TinyMinify'  => 'jenstornell/tiny-html-minifier.php'
);
try {
    spl_autoload_register(function ($class) use ($plugin_lib, $libsPathClass) {
        if (strpos($class, 'PHPWee\\') === 0) {
            $e = explode('\\', $class);
            require_once $plugin_lib . '/phpwee-php-minifier/' . $e[1] . '/' . $e[1] . '.php';
        } elseif (array_key_exists($class, $libsPathClass)) {
            require_once $plugin_lib . '/' . $libsPathClass[$class];
        }
    });
} catch (Exception $e) {
    //
}

/**
 * Call hooks plugins
 */
register_activation_hook(__FILE__, array('CdnMinifierPlugin', 'hook_activation'));
register_deactivation_hook(__FILE__, array('CdnMinifierPlugin', 'hook_deactivation'));
register_uninstall_hook(__FILE__, array('CdnMinifierPlugin', 'hook_uninstall'));

/**
 *
 */
add_action('plugins_loaded', array('CdnMinifierPlugin', 'run'));