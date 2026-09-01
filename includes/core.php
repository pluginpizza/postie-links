<?php
/**
 * Core plugin functionality
 *
 * @package PostieLinksAddOn
 */

namespace PluginPizza\PostieLinksAddOn;

use function PluginPizza\PostieLinksAddOn\Helpers\force_https_scheme;
use function PluginPizza\PostieLinksAddOn\Helpers\is_url;
use function PluginPizza\PostieLinksAddOn\Helpers\remove_utm_query_args;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
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
 * @return array
 */
function maybe_update_post( $post ) {

	$post_content = trim( wp_strip_all_tags( $post['post_content'], true ) );

	if ( strpos( $post_content, ' ' ) ) {
		return $post;
	}

	$post_content = esc_url( $post_content );
	$post_content = force_https_scheme( $post_content );

	if ( ! is_url( $post_content ) ) {
		return $post;
	}

	/**
	 * Allows disabling the removal of 'utm' query parameters from the URL.
	 *
	 * @var bool   $strip_utm    Whether to remove the 'utm' parameters.
	 * @var string $post_content URL.
	 */
	if ( apply_filters( 'pluginpizza_postie_links_remove_utm', true, $post_content ) ) {
		$post_content = remove_utm_query_args( $post_content );
	}

	/**
	 * Allows filtering the URL before meta, format, and content are updated.
	 *
	 * @var string $post_content URL.
	 */
	$post_content = apply_filters( 'pluginpizza_postie_links_url', $post_content );

	add_post_meta( $post['ID'], 'pluginpizza_postie_links_url', $post_content );

	/**
	 * Allows disabling or overriding the post format.
	 *
	 * @var bool   $post_format Post format, return false to not set a post format.
	 * @var string $url         URL.
	 * @var array  $post        List of post fields.
	 */
	$post_format = apply_filters( 'pluginpizza_postie_links_post_format', 'link', $post_content, $post );

	if ( $post_format ) {

		set_post_format( $post['ID'], (string) $post_format );
	}

	$post_content = sprintf(
		'<!-- wp:paragraph --><p><a href="%s">%s</a></p><!-- /wp:paragraph -->',
		esc_url( $post_content ),
		wp_kses_post( $post_content )
	);

	$post['post_content'] = $post_content;

	/**
	 * Allows filtering the post array before the post is updated.
	 *
	 * @var array  $post         List of post fields with updated 'post_content' item.
	 * @var string $post_content URL.
	 */
	$post = apply_filters(
		'pluginpizza_postie_links_post',
		$post,
		$post_content
	);

	return $post;
}
