<?php
/**
 * single.php — Single post template.
 * Schema.org Article, related posts by category, comments.
 *
 * @package tso-blog
 */
get_header();

while ( have_posts() ) :
	the_post();

	$tsothm_post_title    = get_the_title();
	$tsothm_post_date_iso = get_the_date( 'c' );
	$tsothm_post_date_mod = get_the_modified_date( 'c' );
	$tsothm_post_author   = get_the_author();
	$tsothm_post_excerpt  = get_the_excerpt();
	$tsothm_post_url      = get_permalink();

	$tsothm_schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'NewsArticle',
		'headline'      => $tsothm_post_title,
		'description'   => wp_strip_all_tags( $tsothm_post_excerpt ),
		'datePublished' => $tsothm_post_date_iso,
		'dateModified'  => $tsothm_post_date_mod,
		'url'           => $tsothm_post_url,
		'author'        => array(
			'@type' => 'Person',
			'name'  => $tsothm_post_author,
		),
		'publisher'     => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( has_post_thumbnail() ) {
		$tsothm_thumb_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		if ( $tsothm_thumb_src ) {
			$tsothm_schema['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $tsothm_thumb_src[0],
				'width'  => (int) $tsothm_thumb_src[1],
				'height' => (int) $tsothm_thumb_src[2],
			);
		}
	}

	wp_print_inline_script_tag(
		wp_json_encode(
			$tsothm_schema,
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		),
		array(
			'type' => 'application/ld+json',
		)
	);
	?>

	<?php get_template_part( 'template-parts/single', 'breadcrumb' ); ?>

<div class="single-layout">

	<main id="primary" class="single-content-area" role="main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope itemtype="https://schema.org/NewsArticle">

			<?php get_template_part( 'template-parts/single', 'header' ); ?>

			<div class="entry-content" itemprop="articleBody">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before'      => '<div class="page-links">' . esc_html__( 'Páginas:', 'tso-blog' ),
						'after'       => '</div>',
						'link_before' => '<span class="page-number">',
						'link_after'  => '</span>',
					)
				);
				?>
			</div>

			<?php get_template_part( 'template-parts/single', 'tags' ); ?>

		</article>

		<?php get_template_part( 'template-parts/single', 'related' ); ?>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>

	</main>

	<?php get_sidebar(); ?>

</div>

	<?php
endwhile;

get_footer();
