<?php
/**
 * SEO meta.
 *
 * Split out of functions.php; functions.php loads every inc/NN-*.php
 * file in numeric order, which is the order the code always ran in.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * 10. SEO meta
 * ====================================================================== */

/**
 * Whether an SEO plugin is doing this job instead.
 *
 * Yoast and Rank Math write the same tags. Two descriptions and two
 * canonicals are worse than either one alone, so everything in this
 * section stands down when one of them is active.
 *
 * @return bool
 */
function rs_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' );
}

/**
 * Google Search Console's ownership tag.
 *
 * Google only reads this on the address the property was registered under,
 * but it costs a single line to put it on every page, and doing so means a
 * property added later for a subsection verifies without another edit.
 *
 * Not gated on rs_seo_plugin_active(): this proves who owns the site
 * rather than describing it, so there is nothing here to duplicate.
 */
function rs_verify_tag() {
	$code = trim( (string) rs_option( 'rs_verify' ) );

	if ( '' === $code ) {
		return;
	}

	printf( "<meta name=\"google-site-verification\" content=\"%s\">\n", esc_attr( $code ) );
}
add_action( 'wp_head', 'rs_verify_tag', 1 );

/**
 * The first image in a post's body.
 *
 * WordPress stamps every image inserted from the media library with a
 * wp-image-<id> class. That is worth more than the src beside it: an ID
 * gives the right size and its dimensions, and it survives the site being
 * moved to another domain. An image pasted in as a bare URL carries no
 * such class and is not found, which is the correct outcome — it may not
 * be ours to put on a share card.
 *
 * @param WP_Post $post Post object.
 * @return int Attachment ID, or 0.
 */
function rs_content_image_id( $post ) {
	if ( ! preg_match( '/wp-image-(\d+)/', $post->post_content, $found ) ) {
		return 0;
	}

	return (int) $found[1];
}

/**
 * The picture to attach to a shared link.
 *
 * Three places to look, in order of how deliberately the picture was
 * chosen: the featured image, then the first image in the body, then the
 * one set in the customizer.
 *
 * The middle one is there because this theme never displays a featured
 * image. Setting one would be invisible work done purely for Facebook's
 * benefit, and a post that already opens with a picture has said which
 * picture it is about.
 *
 * @return array|null
 */
function rs_share_image() {
	$id = 0;

	/* A project page shares its own artwork, when that is a picture
	   social networks can show (they skip SVG) and its size is known. */
	$project = function_exists( 'rs_current_project' ) ? rs_current_project() : null;

	if ( $project && preg_match( '~\.(png|jpe?g|webp)(?:\?.*)?$~i', (string) $project['image'] ) ) {
		$path = str_replace( get_template_directory_uri(), get_template_directory(), strtok( $project['image'], '?' ) );
		$size = ( $path !== $project['image'] && is_readable( $path ) ) ? getimagesize( $path ) : false;

		if ( $size ) {
			return array(
				'url'    => $project['image'],
				'width'  => (int) $size[0],
				'height' => (int) $size[1],
			);
		}
	}

	if ( is_singular() ) {
		$post = get_post( get_queried_object_id() );

		if ( $post && has_post_thumbnail( $post ) ) {
			$id = get_post_thumbnail_id( $post );
		} elseif ( $post ) {
			$id = rs_content_image_id( $post );
		}
	}

	if ( ! $id ) {
		$id = (int) rs_option( 'rs_og_image' );
	}

	if ( ! $id ) {
		return null;
	}

	/*
	 * The medium size deliberately, and the smallness is the point.
	 *
	 * Facebook chooses the shape of the card from the picture it is
	 * handed. At 600x315 or above it builds the tall card: a wide image,
	 * the headline under it, and nothing else. Below that it builds the
	 * compact one — a small picture on the left, and on the right the
	 * headline followed by the opening lines of the writing.
	 *
	 * What is being shared here is prose, so the card that shows a few
	 * lines of it is worth more than the one that fills the feed with a
	 * picture. Sending a large image would silently throw those lines
	 * away, which is exactly what it did until this was changed.
	 *
	 * Reversed later: the wide card is what most readers now share from,
	 * and the compact one was being cropped to a thumbnail on X. The
	 * 1200x630 cut goes first; a picture too small for it keeps the old
	 * medium size and the compact card.
	 */
	$src = wp_get_attachment_image_src( $id, 'rs-share' );

	if ( ! $src || (int) $src[1] < 1200 ) {
		$src = wp_get_attachment_image_src( $id, 'medium' );
	}

	if ( ! $src ) {
		return null;
	}

	return array(
		'url'    => $src[0],
		'width'  => (int) $src[1],
		'height' => (int) $src[2],
	);
}

/**
 * What the view on screen should say about itself.
 *
 * Null for the views that have nothing worth saying — a date or author
 * archive repeats the front page, and rs_robots() below keeps those out of
 * the index rather than describing them.
 *
 * @return array|null
 */
function rs_seo_context() {
	$paged = max( 1, (int) get_query_var( 'paged' ) );

	/* A project's own address describes the project, not the portfolio. */
	$project = function_exists( 'rs_current_project' ) ? rs_current_project() : null;

	if ( $project ) {
		$name = rs_project_name( $project );

		return array(
			'title'       => $name[1] ? $name[0] . ': ' . $name[1] : $name[0],
			'description' => rs_shorten( rs_is_en() ? $project['summary_en'] : $project['summary_bn'], 160 ),
			'url'         => rs_project_url( $project['id'] ),
			'type'        => 'article',
		);
	}

	if ( is_singular() ) {
		return array(
			'title'       => get_the_title( get_queried_object_id() ),
			'description' => rs_summary( get_queried_object_id(), 160 ),
			'url'         => get_permalink( get_queried_object_id() ),
			'type'        => is_singular( 'post' ) ? 'article' : 'website',
		);
	}

	if ( is_category() || is_tag() ) {
		$term = get_queried_object();

		if ( ! $term instanceof WP_Term ) {
			return null;
		}

		$base = get_term_link( $term );

		if ( is_wp_error( $base ) ) {
			return null;
		}

		$about = trim( wp_strip_all_tags( $term->description ) );

		if ( '' === $about ) {
			if ( rs_is_en() ) {
				$pattern = is_category() ? 'Everything written in %1$s — %2$s' : 'Everything written about %1$s — %2$s';
			} else {
				$pattern = is_category() ? '%1$s বিভাগের সব লেখা — %2$s' : '%1$s বিষয়ের সব লেখা — %2$s';
			}

			$about = sprintf( $pattern, $term->name, get_bloginfo( 'name' ) );
		}

		return array(
			'title'       => $term->name,
			'description' => rs_shorten( $about, 160 ),
			/* Page one keeps the clean term URL. Running it through
			   get_pagenum_link() would drag any utm_ tags the reader
			   arrived with into the canonical. */
			'url'         => $paged > 1 ? rs_page_url( $paged ) : $base,
			'type'        => 'website',
		);
	}

	if ( is_home() || is_front_page() ) {
		$site_name = get_bloginfo( 'name' );
		$tagline   = get_bloginfo( 'description' );

		/* The tail sentence is what a search result prints under the title, so
		   it has to be in the language of the site it is describing. */
		$tail = rs_is_en()
			? '. The personal website and blog of Raisul Sohan.'
			: '। রাইসুল সোহানের ব্যক্তিগত ওয়েবসাইট ও ব্লগ (Personal Website & Blog)।';

		return array(
			'title'       => $site_name . ' - ' . $tagline,
			/* Cut like every other description here: search engines show about
			   160 characters, and a long tagline pushed the tail past that. */
			'description' => rs_shorten( $site_name . ' - ' . $tagline . $tail, 160 ),
			'url'         => $paged > 1 ? rs_page_url( $paged ) : home_url( '/' ),
			'type'        => 'website',
		);
	}

	return null;
}

/**
 * Description, canonical, Open Graph and Twitter tags.
 */
function rs_seo_meta() {
	if ( rs_seo_plugin_active() ) {
		return;
	}

	$view = rs_seo_context();

	if ( ! $view ) {
		return;
	}

	$image = rs_share_image();

	printf( "\n<meta name=\"description\" content=\"%s\">\n", esc_attr( $view['description'] ) );

	/* Core writes a canonical on single posts and pages and nowhere else,
	   which was enough while those were the only addresses the theme had.
	   Now that the list paginates, page two has to name itself or it reads
	   as a second copy of the front page. */
	if ( ! is_singular() ) {
		printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $view['url'] ) );
	}

	printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( $view['type'] ) );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $view['title'] ) );
	printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $view['description'] ) );
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $view['url'] ) );
	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( get_locale() ) );

	if ( is_singular( 'post' ) ) {
		$id     = get_queried_object_id();
		$author = (int) get_post_field( 'post_author', $id );

		printf( "<meta property=\"article:published_time\" content=\"%s\">\n", esc_attr( get_the_date( DATE_W3C, $id ) ) );
		printf( "<meta property=\"article:modified_time\" content=\"%s\">\n", esc_attr( get_the_modified_date( DATE_W3C, $id ) ) );
		printf( "<meta property=\"article:author\" content=\"%s\">\n", esc_attr( rs_author_name( $author ) ) );

		$section = rs_category( $id );

		if ( $section ) {
			printf( "<meta property=\"article:section\" content=\"%s\">\n", esc_attr( $section ) );
		}
	}

	if ( $image ) {
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $image['url'] ) );
		printf( "<meta property=\"og:image:width\" content=\"%d\">\n", (int) $image['width'] );
		printf( "<meta property=\"og:image:height\" content=\"%d\">\n", (int) $image['height'] );
	}

	/* The small card here too, to match what rs_share_image() asks
	   Facebook for. summary_large_image would stretch a medium sized
	   picture across a wide frame and drop the description underneath it,
	   which is the trade this theme has already declined once. */
	printf( "<meta name=\"twitter:card\" content=\"%s\">\n", ( ! empty( $image ) && (int) $image['width'] >= 600 ) ? 'summary_large_image' : 'summary' );
	printf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $view['title'] ) );
	printf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $view['description'] ) );
	printf( "<meta name=\"twitter:site\" content=\"@raisulsohan\">\n" );
	printf( "<meta name=\"twitter:creator\" content=\"@raisulsohan\">\n" );
	if ( $image ) {
		printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $image['url'] ) );
	}
}
add_action( 'wp_head', 'rs_seo_meta', 1 );

/**
 * The trail from the front page to whatever is being shown.
 *
 * This is the half of structured data a reader actually sees: given it,
 * Google prints "raisulsohan.com › গল্প › …" above a result instead of the
 * bare address. It belongs on the post as much as on the archive — the post
 * is the page that turns up in a search, so the post is the page whose
 * result the trail improves.
 *
 * The last step carries no "item" on purpose. It is the page already being
 * read, and schema.org treats a trail that ends without a link as ending
 * here rather than pointing somewhere else.
 *
 * @return array|null BreadcrumbList node, or null where there is no trail.
 */
function rs_breadcrumb_schema() {
	$items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => get_bloginfo( 'name' ),
			'item'     => home_url( '/' ),
		),
	);

	if ( is_singular( 'post' ) ) {
		$term = rs_primary_category();

		if ( $term ) {
			$link = get_term_link( $term );

			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => $term->name,
				/* get_term_link() answers with an error object when a term
				   has lost its taxonomy; a step without an address is still
				   a usable step. */
				'item'     => is_wp_error( $link ) ? null : $link,
			);
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => count( $items ) + 1,
			/* Decoded for the same reason the headline above is: this is
			   JSON, and an "&#8217;" would reach Google as those characters
			   rather than as the apostrophe it stands for. */
			'name'     => html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ),
		);
	} elseif ( is_category() || is_tag() ) {
		$term = get_queried_object();

		if ( ! $term || is_wp_error( $term ) || empty( $term->name ) ) {
			return null;
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => $term->name,
		);
	} else {
		return null;
	}

	/* A trail of one step is the front page pointing at itself. */
	if ( count( $items ) < 2 ) {
		return null;
	}

	foreach ( $items as $index => $item ) {
		if ( array_key_exists( 'item', $item ) && null === $item['item'] ) {
			unset( $items[ $index ]['item'] );
		}
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => array_values( $items ),
	);
}

/**
 * JSON-LD, so a search engine can tell a story from a page about stories.
 *
 * Person rather than Organization as the publisher: this is one writer's
 * site, and saying otherwise would invite a logo that does not exist.
 */
function rs_schema() {
	if ( rs_seo_plugin_active() ) {
		return;
	}

	$data = null;

	if ( is_singular( 'post' ) ) {
		$id     = get_queried_object_id();
		$author = (int) get_post_field( 'post_author', $id );
		$image  = rs_share_image();

		$data = array(
			'@type'            => 'BlogPosting',
			/*
			 * schema.org asks for 110 characters or fewer here.
			 *
			 * Decoded first, because this is JSON and not HTML: wptexturize
			 * turns a hyphen in a title into "&#8211;", which an attribute
			 * would decode on the way out but a JSON string would hand to
			 * Google as those eight literal characters. rs_summary() below
			 * is already plain text, so it needs none of this.
			 */
			'headline'         => rs_shorten( html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ), 110 ),
			'description'      => rs_summary( $id, 160 ),
			'datePublished'    => get_the_date( DATE_W3C, $id ),
			'dateModified'     => get_the_modified_date( DATE_W3C, $id ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $id ),
			),
			'author'           => array(
				'@type' => 'Person',
				'name'  => rs_author_name( $author ),
				'url'   => home_url( '/' ),
			),
			'publisher'        => array(
				'@type' => 'Person',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		$section = rs_category( $id );

		if ( $section ) {
			$data['articleSection'] = $section;
		}

		if ( $image ) {
			$data['image'] = $image['url'];
		}
	} elseif ( is_home() || is_front_page() ) {
		$data = array(
			'@type'       => 'WebSite',
			'name'        => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'url'         => home_url( '/' ),
			'inLanguage'  => get_bloginfo( 'language' ),
			/*
			 * The site's own search, described so a machine can use it.
			 *
			 * Google stopped drawing a search box under a result for this
			 * in late 2023, so it is not the reason to keep it. It stays
			 * because it is true, it costs nine lines, and it is still read
			 * by everything else that reads structured data.
			 */
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	/* Built for the same pages either way, and appended rather than folded
	   in, because a trail is its own thing and not a property of the post. */
	$graph = array();

	if ( $data ) {
		$graph[] = $data;
	}

	$crumbs = rs_breadcrumb_schema();

	if ( $crumbs ) {
		$graph[] = $crumbs;
	}

	if ( ! $graph ) {
		return;
	}

	/* One object stays one object; more than one goes in a @graph. Either
	   way @context is stated once, at the top. */
	$payload = array( '@context' => 'https://schema.org' );

	if ( 1 === count( $graph ) ) {
		$payload = array_merge( $payload, $graph[0] );
	} else {
		$payload['@graph'] = $graph;
	}

	/* Unicode is left alone so the Bengali stays readable in view source,
	   but slashes keep their escaping: that is what stops a "</script>" in
	   a title from closing this block early. */
	printf(
		"\n<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}
add_action( 'wp_head', 'rs_schema', 2 );

/**
 * Keep the archives that repeat the front page out of the index.
 *
 * A date or an author archive on a one writer site is the same posts in
 * the same order with nothing added, and every one of them competes with
 * the pages that matter. Search results core already handles on its own.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function rs_robots( $robots ) {
	if ( rs_seo_plugin_active() ) {
		return $robots;
	}

	if ( is_date() || is_author() ) {
		$robots['noindex'] = true;
		/* Still worth crawling through: the links out of here are the
		   posts themselves. */
		$robots['follow'] = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'rs_robots' );

/**
 * Keep the author archive out of the sitemap.
 *
 * A sitemap is a list of pages worth indexing, and rs_robots() above has
 * just told Google not to index this one. Offering it anyway is a
 * contradiction, and Search Console reports it back as an excluded URL.
 *
 * @param WP_Sitemaps_Provider $provider Sitemap provider.
 * @param string               $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function rs_sitemap_providers( $provider, $name ) {
	/* The noindex is conditional on this same check, so the omission has
	   to be: with a plugin in charge the archive may well be indexable. */
	if ( rs_seo_plugin_active() ) {
		return $provider;
	}

	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'rs_sitemap_providers', 10, 2 );

/**
 * Add a helpful body class.
 *
 * @param array $classes Body classes.
 * @return array
 */
function rs_body_class( $classes ) {
	$classes[] = 'rs';

	/* app.js takes over the history on any view that renders a list: it is
	   what tells the popstate handler that a back press means "close the
	   modal" or "go to the previous page of rows", and not "leave". */
	if ( is_home() || is_front_page() || is_archive() || is_search() ) {
		$classes[] = 'rs-is-list';
	}

	if ( get_theme_mod( 'rs_enable_animations', true ) ) {
		$classes[] = 'rs-animated';
	}

	return $classes;
}
add_filter( 'body_class', 'rs_body_class' );
