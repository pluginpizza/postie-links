<?php
/**
 * Plugin Name: Postie Links Add-On
 * Description: An add-on for the <a href="https://wordpress.org/plugins/postie/">Postie</a> plugin. If the email content only contains a URL the post is created with the "Link" format.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.0
 * Text Domain: postie-links
 * Author: Plugin Pizza, Barry Ceelen
 * Author URI: https://plugin.pizza
 * Plugin URI: https://github.com/pluginpizza/postie-links
 * License: GPLv3+
 *
 * @package PostieLinksAddOn
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PLUGINPIZZA_POSTIE_LINKS_ADDON_INC' ) ) {
	define( 'PLUGINPIZZA_POSTIE_LINKS_ADDON_INC', plugin_dir_path( __FILE__ ) . 'includes/' );
}

require_once PLUGINPIZZA_POSTIE_LINKS_ADDON_INC . 'helpers.php';
require_once PLUGINPIZZA_POSTIE_LINKS_ADDON_INC . 'core.php';
