<?php
/**
 * Core plugin functionality
 *
 * @package PostieLinksAddOn
 */

namespace PluginPizza\PostieLinksAddOn;

use function PluginPizza\PostieLinksAddOn\Helpers\extract_url_from_content;
use function PluginPizza\PostieLinksAddOn\Helpers\force_https_scheme;
use function PluginPizza\PostieLinksAddOn\Helpers\is_url;
use function PluginPizza\PostieLinksAddOn\Helpers\remove_utm_query_args;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Update the post if the content is deemed to only contain a URL.
add_filter( 'postie_post_before', __NAMESPACE__ . '\maybe_update_post', 999 );

/**
 * Update the post if the content is deemed to only contain a URL.
 *
 * 1. Upgrade the URL scheme from http to https.
 * 2. Strip 'utm' query parameters.
 * 3. Filter the URL, then save it in the `pluginpizza_postie_links_url` meta field.
 * 4. Set the post format to `link`.
 * 5. Update the post content to contain a paragraph block with a link element.
 *
 * @param array $post An array of elements that make up a post to insert.
 * @return array List of post fields.
 */
function maybe_update_post( $post ) {

	if ( ! is_array( $post ) ) {
		return $post;
	}

	if ( empty( $post['ID'] ) || ! isset( $post['post_content'] ) || ! is_string( $post['post_content'] ) ) {
		return $post;
	}

	$post_content = extract_url_from_content( $post['post_content'] );

	if ( '' === $post_content ) {
		return $post;
	}

	$allowed_protocols = array( 'http', 'https' );
	$post_content      = esc_url_raw( $post_content, $allowed_protocols );
	$post_content      = force_https_scheme( $post_content );

	if ( empty( $post_content ) || ! is_url( $post_content ) ) {
		return $post;
	}

	/**
	 * Filters whether to remove utm_* query parameters from the URL.
	 *
	 * @param bool   $strip_utm    Whether to remove the utm_* parameters.
	 * @param string $post_content URL.
	 */
	if ( apply_filters( 'pluginpizza_postie_links_remove_utm', true, $post_content ) ) {
		$post_content = remove_utm_query_args( $post_content );
	}

	/**
	 * Filters the URL before meta, format, and content are updated.
	 *
	 * @param string $post_content URL.
	 */
	$post_content = apply_filters( 'pluginpizza_postie_links_url', $post_content );
	$post_content = esc_url_raw( $post_content, $allowed_protocols );

	if ( empty( $post_content ) || ! is_url( $post_content ) ) {
		return $post;
	}

	update_post_meta( $post['ID'], 'pluginpizza_postie_links_url', $post_content );

	/**
	 * Filters the post format applied to a URL-only post.
	 *
	 * Return a falsy value to skip setting a post format.
	 *
	 * @param string|false $post_format  Post format, or false to skip.
	 * @param string       $post_content URL.
	 * @param array        $post         List of post fields.
	 */
	$post_format = apply_filters( 'pluginpizza_postie_links_post_format', 'link', $post_content, $post );

	if ( $post_format ) {

		set_post_format( $post['ID'], (string) $post_format );
	}

	$post_content = sprintf(
		'<!-- wp:paragraph --><p><a href="%s">%s</a></p><!-- /wp:paragraph -->',
		esc_url( $post_content ),
		esc_html( $post_content )
	);

	$post['post_content'] = $post_content;

	/**
	 * Filters the post array before Postie saves it.
	 *
	 * @param array  $post         List of post fields with updated post_content.
	 * @param string $post_content Post content (paragraph block with the URL).
	 */
	$post = apply_filters(
		'pluginpizza_postie_links_post',
		$post,
		$post_content
	);

	return $post;
}
