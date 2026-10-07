<?php
/**
 * Template Name: Contacto
 * Template Post Type: page
 * Description: Página de contacto con datos, formulario (shortcode) y Google Maps.
 *
 * @package tso-blog
 */

get_header();

$tsothm_contact = tsothm_get_contact_settings();
$tsothm_hero_bg = '';
if ( has_post_thumbnail() ) {
	$tsothm_hero_bg = get_the_post_thumbnail_url( get_the_ID(), 'large' );
}
?>

<div class="tso-contact-page">
	<section class="tso-contact-hero"<?php echo $tsothm_hero_bg ? ' style="--tso-contact-hero-image:url(' . esc_url( $tsothm_hero_bg ) . ')"' : ''; ?>>
		<div class="tso-contact-hero-inner">
			<?php while ( have_posts() ) : the_post(); ?>
				<h1 class="tso-contact-hero-title"><?php the_title(); ?></h1>
				<?php if ( $tsothm_contact['subtitle'] ) : ?>
					<p class="tso-contact-hero-subtitle"><?php echo esc_html( $tsothm_contact['subtitle'] ); ?></p>
				<?php endif; ?>
			<?php endwhile; ?>
		</div>
	</section>

	<div class="tso-contact-wrap">
		<div class="tso-contact-card">
			<div class="tso-contact-info">
				<h2 class="tso-contact-card-title"><?php esc_html_e( 'Ponte en contacto', 'tso-blog' ); ?></h2>
				<?php if ( $tsothm_contact['intro'] ) : ?>
					<p class="tso-contact-intro"><?php echo esc_html( $tsothm_contact['intro'] ); ?></p>
				<?php endif; ?>

				<ul class="tso-contact-details">
					<?php if ( $tsothm_contact['address'] ) : ?>
						<li class="tso-contact-detail">
							<span class="tso-contact-detail-icon" aria-hidden="true">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" focusable="false"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>
							</span>
							<div>
								<strong><?php esc_html_e( 'Oficina', 'tso-blog' ); ?></strong>
								<p><?php echo nl2br( esc_html( $tsothm_contact['address'] ) ); ?></p>
							</div>
						</li>
					<?php endif; ?>

					<?php if ( $tsothm_contact['email'] || $tsothm_contact['email_alt'] ) : ?>
						<li class="tso-contact-detail">
							<span class="tso-contact-detail-icon" aria-hidden="true">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" focusable="false"><path fill="currentColor" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
							</span>
							<div>
								<strong><?php esc_html_e( 'Email', 'tso-blog' ); ?></strong>
								<p>
									<?php if ( $tsothm_contact['email'] ) : ?>
										<a href="<?php echo esc_url( 'mailto:' . $tsothm_contact['email'] ); ?>"><?php echo esc_html( $tsothm_contact['email'] ); ?></a>
									<?php endif; ?>
									<?php if ( $tsothm_contact['email'] && $tsothm_contact['email_alt'] ) : ?>
										<br>
									<?php endif; ?>
									<?php if ( $tsothm_contact['email_alt'] ) : ?>
										<a href="<?php echo esc_url( 'mailto:' . $tsothm_contact['email_alt'] ); ?>"><?php echo esc_html( $tsothm_contact['email_alt'] ); ?></a>
									<?php endif; ?>
								</p>
							</div>
						</li>
					<?php endif; ?>

					<?php if ( $tsothm_contact['phone'] || $tsothm_contact['phone_alt'] ) : ?>
						<li class="tso-contact-detail">
							<span class="tso-contact-detail-icon" aria-hidden="true">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" focusable="false"><path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.4 21 3 13.6 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.46.57 3.58a1 1 0 0 1-.25 1.02l-2.2 2.19z"/></svg>
							</span>
							<div>
								<strong><?php esc_html_e( 'Teléfono', 'tso-blog' ); ?></strong>
								<p>
									<?php if ( $tsothm_contact['phone'] ) : ?>
										<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $tsothm_contact['phone'] ) ); ?>"><?php echo esc_html( $tsothm_contact['phone'] ); ?></a>
									<?php endif; ?>
									<?php if ( $tsothm_contact['phone'] && $tsothm_contact['phone_alt'] ) : ?>
										<br>
									<?php endif; ?>
									<?php if ( $tsothm_contact['phone_alt'] ) : ?>
										<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $tsothm_contact['phone_alt'] ) ); ?>"><?php echo esc_html( $tsothm_contact['phone_alt'] ); ?></a>
									<?php endif; ?>
								</p>
							</div>
						</li>
					<?php endif; ?>
				</ul>

				<?php if ( ! empty( $tsothm_contact['show_social'] ) ) : ?>
					<div class="tso-contact-social">
						<strong class="tso-contact-social-title"><?php esc_html_e( 'Síguenos', 'tso-blog' ); ?></strong>
						<?php tsothm_social_icons(); ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="tso-contact-form-col">
				<h2 class="tso-contact-card-title"><?php esc_html_e( 'Envíanos un mensaje', 'tso-blog' ); ?></h2>
				<div class="tso-contact-form">
					<?php if ( $tsothm_contact['form_shortcode'] ) : ?>
						<?php echo do_shortcode( $tsothm_contact['form_shortcode'] ); ?>
					<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
						<p class="tso-contact-form-hint">
							<?php esc_html_e( 'Añade el shortcode de tu formulario en Apariencia → Personalizar → Contacto (p. ej. Contact Form 7). El tema no envía correos por sí mismo.', 'tso-blog' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ( $tsothm_contact['maps_embed'] ) : ?>
			<?php tsothm_render_contact_map( $tsothm_contact['maps_embed'] ); ?>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
