<?php
/**
 * Plugin helper functions
 *
 * @package PostieLinksAddOn
 */

namespace PluginPizza\PostieLinksAddOn\Helpers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Is the string a URL?
 *
 * @todo Some mail programs create a hyperlink when inserting a URL. We'll
 *       probably also want to check if the email body contains a hyperlink.
 *
 * @param string $string String of text.
 * @return bool
 */
function is_url( $string ) {

	if ( filter_var( $string, FILTER_VALIDATE_URL ) ) {
		return true;
	}

	require_once( PLUGINPIZZA_POSTIE_LINKS_ADDON_INC . 'class-idna-convert.php' );

	$idna = new \idna_convert( array( 'idn_version' => '2008' ) );

	if ( filter_var( $idna->encode( $string, 'utf8' ), FILTER_VALIDATE_URL ) ) {
		return true;
	}

	return false;
}

/**
 * Upgrade an http URL scheme to https.
 *
 * @param string $url URL.
 * @return string
 */
function force_https_scheme( $url ) {

	if ( 0 !== stripos( $url, 'http://' ) ) {
		return $url;
	}

	return 'https://' . substr( $url, 7 );
}

/**
 * Remove utm_* query parameters from a URL.
 *
 * @see https://en.wikipedia.org/wiki/UTM_parameters
 *
 * @param string $url URL.
 * @return string
 */
function remove_utm_query_args( $url ) {

	$query = wp_parse_url( $url, PHP_URL_QUERY );

	if ( empty( $query ) ) {
		return $url;
	}

	wp_parse_str( $query, $args );

	$utm_keys = array();

	foreach ( array_keys( $args ) as $key ) {
		if ( 0 === strpos( $key, 'utm_' ) ) {
			$utm_keys[] = $key;
		}
	}

	if ( empty( $utm_keys ) ) {
		return $url;
	}

	return remove_query_arg( $utm_keys, $url );
}
