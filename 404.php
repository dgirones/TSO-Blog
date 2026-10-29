<?php
/**
 * 404.php — Not found.
 *
 * @package tso-blog
 */
get_header();
?>

<main id="primary" class="error-404 main-content-area" role="main">
	<div class="error-404-inner">
		<p class="error-404-code" aria-hidden="true">404</p>
		<h1 class="error-404-title"><?php esc_html_e( 'Page not found', 'tso-blog' ); ?></h1>
		<p class="error-404-text">
			<?php esc_html_e( 'It looks like what you are looking for is not here. Try searching or go back home.', 'tso-blog' ); ?>
		</p>

		<div class="error-404-search">
			<?php get_search_form(); ?>
		</div>

		<p class="error-404-home">
			<a class="error-404-home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to home', 'tso-blog' ); ?>
			</a>
		</p>
	</div>
</main>

<?php
get_footer();
