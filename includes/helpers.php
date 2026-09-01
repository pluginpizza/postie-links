<?php
/**
 * Plugin helper functions
 *
 * @package PostieLinksAddOn
 */

namespace PluginPizza\PostieLinksAddOn\Helpers;

use WP_HTML_Tag_Processor;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extract a single URL from email or post content.
 *
 * Accepts a lone URL (including line-wrapped URLs) or content whose only
 * meaningful markup is one `<a href>`. Extra prose returns an empty string.
 *
 * @param string $content Post content.
 * @return string URL or an empty string.
 */
function extract_url_from_content( $content ) {

	$href = extract_single_anchor_href( $content );

	if ( '' !== $href ) {
		return $href;
	}

	$text = wp_strip_all_tags( $content );
	$text = str_replace( "\xC2\xA0", ' ', $text );
	$text = preg_replace( '/[\r\n\t]+/', '', $text );
	$text = trim( $text );

	if ( '' === $text || false !== strpos( $text, ' ' ) ) {
		return '';
	}

	return $text;
}

/**
 * Return the href when content is a single anchor and nothing else.
 *
 * Uses the WordPress HTML API (WP 6.5+) so attribute decoding and leftover
 * text detection do not depend on regular expressions.
 *
 * @param string $content Post content.
 * @return string href or an empty string.
 */
function extract_single_anchor_href( $content ) {

	$processor    = new WP_HTML_Tag_Processor( $content );
	$href         = '';
	$anchor_depth = 0;

	while ( $processor->next_token() ) {
		$type = $processor->get_token_type();

		if ( '#tag' === $type ) {
			if ( 'A' !== $processor->get_token_name() ) {
				continue;
			}

			if ( $processor->is_tag_closer() ) {
				if ( $anchor_depth > 0 ) {
					--$anchor_depth;
				}
				continue;
			}

			$found = $processor->get_attribute( 'href' );

			if ( null === $found || '' === $found ) {
				continue;
			}

			if ( '' !== $href ) {
				return '';
			}

			$href = (string) $found;
			++$anchor_depth;
			continue;
		}

		if ( '#text' !== $type || 0 !== $anchor_depth ) {
			continue;
		}

		$text = str_replace( "\xC2\xA0", ' ', $processor->get_modifiable_text() );

		if ( '' !== trim( $text ) ) {
			return '';
		}
	}

	return $href;
}

/**
 * Is the string a URL?
 *
 * @param string $url_string String of text.
 * @return bool Whether the string is a URL.
 */
function is_url( $url_string ) {

	if ( filter_var( $url_string, FILTER_VALIDATE_URL ) ) {
		return true;
	}

	require_once PLUGINPIZZA_POSTIE_LINKS_ADDON_INC . 'class-idna-convert.php';

	$idna = new \idna_convert( array( 'idn_version' => '2008' ) );

	if ( filter_var( $idna->encode( $url_string, 'utf8' ), FILTER_VALIDATE_URL ) ) {
		return true;
	}

	return false;
}

/**
 * Upgrade an http URL scheme to https.
 *
 * @param string $url URL.
 * @return string URL with an https scheme when the input used http.
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
 * @return string URL without utm_* query parameters.
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
