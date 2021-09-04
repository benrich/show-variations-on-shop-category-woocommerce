<?php

/**
 * Plugin Name: Show Single Variations Shop & Category for WooCommerce
 * Description: Show all different variation as single product in shop page and category page.
 * Version:     1.0
 * Author:      Ben Rich
 * Inspiration: https://wordpress.org/plugins/woo-show-single-variations-shop-category/
 * License:     GPLv2 or later
 * Text Domain: gmwsvs
 */

/* Stop immediately if accessed directly. */
if (!defined('ABSPATH')) die();

if (!defined('WSSVSC_PLUGINDIR')) {
	define('WSSVSC_PLUGINDIR', plugin_dir_path(__FILE__));
}
if (!defined('WSSVSC_PLUGINURL')) {
	define('WSSVSC_PLUGINURL', plugin_dir_url(__FILE__));
}

/* Auto-load all the necessary classes. */
if (!function_exists('wssvsc_class_auto_loader')) {
	function wssvsc_class_auto_loader($class)
	{
		$includes = WSSVSC_PLUGINDIR . 'includes/' . $class . '.php';

		if (is_file($includes) && !class_exists($class)) {
			include_once($includes);
			return;
		}
	}
}

spl_autoload_register('wssvsc_class_auto_loader');

// $wssvsc_cron = new WSSVSC_Cron();
$wssvsc_admin = new WSSVSC_Admin();
$wssvsc_front = new WSSVSC_Frontend();

// https://wordpress.stackexchange.com/questions/36013/remove-action-or-remove-filter-with-external-classes
