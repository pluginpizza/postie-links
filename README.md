# Postie Links Add-On

An add-on for the [Postie](https://wordpress.org/plugins/postie/) plugin. If the email content only contains a URL, the post is created with the Link format.

Requires WordPress 6.5 or later and PHP 7.0 or later.

## Post meta

When a URL-only email is processed, the cleaned URL is stored on the post as `pluginpizza_postie_links_url`.

## Hooks

The add-on runs on Postie’s `postie_post_before` filter at priority 999. The following hooks let you change that behavior.

### `pluginpizza_postie_links_remove_utm`

Whether to strip `utm_*` query parameters from the URL. Default is `true`.

```php
add_filter( 'pluginpizza_postie_links_remove_utm', '__return_false' );
```

Arguments: `bool $strip_utm`, `string $url`.

### `pluginpizza_postie_links_url`

Filters the URL after scheme/UTM cleanup and before it is saved to meta, used as the post format argument, and written into post content.

```php
add_filter(
	'pluginpizza_postie_links_url',
	function ( $url ) {
		return $url;
	}
);
```

Arguments: `string $url`.

### `pluginpizza_postie_links_post_format`

Filters the post format. Default is `link`. Return a falsy value to skip setting a format.

```php
add_filter( 'pluginpizza_postie_links_post_format', '__return_false' );
```

Arguments: `string|false $post_format`, `string $url`, `array $post`.

### `pluginpizza_postie_links_post`

Filters the post array after the paragraph block has been set, immediately before it is returned to Postie.

```php
add_filter(
	'pluginpizza_postie_links_post',
	function ( $post, $content ) {
		return $post;
	},
	10,
	2
);
```

Arguments: `array $post`, `string $content` (the paragraph block containing the URL).
