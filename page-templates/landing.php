<?php
/**
 * Template Name: Landing / Portada
 * Template Post Type: page
 * Description: Portada sin barra lateral con cabecera destacada (imagen destacada + título + extracto) seguida del contenido a base de bloques.
 *
 * @package tso-blog
 */

get_header();

$tsothm_landing_bg   = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '';
$tsothm_landing_ex   = get_the_excerpt();
$tsothm_landing_hero = tsothm_get_landing_hero_settings();
?>

<div class="tso-landing-page">
	<?php while ( have_posts() ) : the_post(); ?>

		<section class="tso-landing-hero"<?php echo $tsothm_landing_bg ? ' style="--tso-landing-hero-image:url(' . esc_url( $tsothm_landing_bg ) . ')"' : ''; ?>>
			<div class="tso-landing-hero-inner">
				<?php if ( $tsothm_landing_hero['eyebrow'] ) : ?>
					<p class="tso-landing-hero-eyebrow"><?php echo esc_html( $tsothm_landing_hero['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h1 class="tso-landing-hero-title"><?php the_title(); ?></h1>
				<?php if ( $tsothm_landing_ex ) : ?>
					<p class="tso-landing-hero-subtitle"><?php echo esc_html( $tsothm_landing_ex ); ?></p>
				<?php endif; ?>
				<?php if ( $tsothm_landing_hero['cta_text'] || $tsothm_landing_hero['cta2_text'] ) : ?>
					<div class="tso-landing-hero-buttons">
						<?php if ( $tsothm_landing_hero['cta_text'] && $tsothm_landing_hero['cta_url'] ) : ?>
							<a class="tso-landing-btn tso-landing-btn--primary" href="<?php echo esc_url( $tsothm_landing_hero['cta_url'] ); ?>"><?php echo esc_html( $tsothm_landing_hero['cta_text'] ); ?></a>
						<?php endif; ?>
						<?php if ( $tsothm_landing_hero['cta2_text'] && $tsothm_landing_hero['cta2_url'] ) : ?>
							<a class="tso-landing-btn tso-landing-btn--secondary" href="<?php echo esc_url( $tsothm_landing_hero['cta2_url'] ); ?>"><?php echo esc_html( $tsothm_landing_hero['cta2_text'] ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<?php tsothm_render_landing_highlights(); ?>

		<div class="main-container tso-landing-content">
			<main id="primary" role="main">
				<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
			</main>
		</div>

	<?php endwhile; ?>
</div>

<?php
get_footer();
